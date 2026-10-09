<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Services\InvoiceNumberService;
use App\Services\PdfSecurityService;
use App\Services\TaxCalculationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InvoiceBuilder extends Component
{
    public ?string $clientId = '';
    public string $invoiceNumber = '';
    public string $issueDate = '';
    public string $dueDate = '';
    public string $currency = 'USD';
    public string $countryTaxCode = 'US';
    public string $taxName = 'VAT';
    public float $taxRate = 0.00;
    public float $discountAmount = 0.00;
    public string $notes = '';
    public string $terms = 'Payment is due within the specified payment terms. Please remit payment via the portal link or wire details below.';
    public bool $isPdfPasswordProtected = false;
    public string $pdfPassword = '';

    public array $items = [];

    // Summary calculations (formatted)
    public float $subtotal = 0.00;
    public float $taxAmount = 0.00;
    public float $totalAmount = 0.00;

    protected TaxCalculationService $taxService;
    protected InvoiceNumberService $numberService;
    protected PdfSecurityService $pdfService;

    public function boot(
        TaxCalculationService $taxService,
        InvoiceNumberService $numberService,
        PdfSecurityService $pdfService
    ) {
        $this->taxService = $taxService;
        $this->numberService = $numberService;
        $this->pdfService = $pdfService;
    }

    public function mount()
    {
        $workspace = Auth::user()->workspace;

        $this->issueDate = date('Y-m-d');
        $this->dueDate = date('Y-m-d', strtotime('+14 days'));
        $this->currency = $workspace->currency ?? 'USD';
        $this->countryTaxCode = $workspace->country_code ?? 'US';

        $this->applyCountryTaxPreset($this->countryTaxCode);

        // Add 1 default empty item
        $this->addItem();
    }

    public function updatedCountryTaxCode($val)
    {
        $this->applyCountryTaxPreset($val);
        $this->recalculate();
    }

    public function updatedClientId($val)
    {
        if ($val) {
            $client = Client::find($val);
            if ($client) {
                if ($client->preferred_currency) {
                    $this->currency = $client->preferred_currency;
                }
                if ($client->country_code) {
                    $this->countryTaxCode = $client->country_code;
                    $this->applyCountryTaxPreset($client->country_code);
                }
            }
        }
        $this->recalculate();
    }

    public function applyCountryTaxPreset(string $countryCode)
    {
        $preset = $this->taxService->getPresetForCountry($countryCode);
        $this->taxName = $preset['name'];
        $this->taxRate = (float) $preset['rate'];
    }

    public function addItem()
    {
        $this->items[] = [
            'service_id' => '',
            'description' => '',
            'category' => 'General',
            'quantity' => 1.0,
            'unit' => 'unit',
            'rate' => 0.00,
            'tax_rate' => $this->taxRate,
            'amount' => 0.00,
        ];
        $this->recalculate();
    }

    public function removeItem(int $index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->recalculate();
        }
    }

    public function selectService(int $index, string $serviceId)
    {
        if (empty($serviceId)) return;

        $service = Service::find($serviceId);
        if ($service && isset($this->items[$index])) {
            $this->items[$index]['service_id'] = $service->id;
            $this->items[$index]['description'] = $service->name . ($service->description ? " - " . $service->description : '');
            $this->items[$index]['category'] = $service->category;
            $this->items[$index]['unit'] = $service->unit;
            $this->items[$index]['rate'] = $service->rate_cents / 100;
            $this->recalculate();
        }
    }

    public function recalculate()
    {
        $subtotalCents = 0;

        foreach ($this->items as $i => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $itemTotal = round($qty * $rate, 2);
            $this->items[$i]['amount'] = $itemTotal;
            $subtotalCents += (int) round($itemTotal * 100);
        }

        $discountCents = (int) round(((float)$this->discountAmount) * 100);

        $client = $this->clientId ? Client::find($this->clientId) : null;

        $calcs = $this->taxService->calculateTotals(
            $subtotalCents,
            $discountCents,
            (float) $this->taxRate,
            $client
        );

        $this->subtotal = $calcs['subtotal_cents'] / 100;
        $this->taxAmount = $calcs['tax_cents'] / 100;
        $this->totalAmount = $calcs['total_cents'] / 100;
    }

    public function saveInvoice(string $status = 'issued')
    {
        $this->validate([
            'clientId' => 'required|exists:clients,id',
            'issueDate' => 'required|date',
            'dueDate' => 'required|date|after_or_equal:issueDate',
            'currency' => 'required|string|size:3',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $workspace = Auth::user()->workspace;
        $client = Client::findOrFail($this->clientId);

        $invoiceNumber = $this->numberService->generateNextNumber($workspace);

        $subtotalCents = (int) round($this->subtotal * 100);
        $discountCents = (int) round($this->discountAmount * 100);
        $taxCents = (int) round($this->taxAmount * 100);
        $totalCents = (int) round($this->totalAmount * 100);

        // Financial snapshot of workspace & client for historical immutability
        $workspaceSnapshot = [
            'name' => $workspace->name,
            'tax_id' => $workspace->tax_id,
            'address' => $workspace->address,
            'phone' => $workspace->phone,
            'email' => $workspace->email,
            'website' => $workspace->website,
            'bank_details' => $workspace->bank_details,
        ];

        $clientSnapshot = [
            'name' => $client->name,
            'company_name' => $client->company_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'tax_id' => $client->tax_id,
            'address' => $client->address,
            'city' => $client->city,
            'country_code' => $client->country_code,
        ];

        $invoice = Invoice::create([
            'workspace_id' => $workspace->id,
            'client_id' => $client->id,
            'invoice_number' => $invoiceNumber,
            'status' => $status,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'currency' => $this->currency,
            'subtotal_cents' => $subtotalCents,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_cents' => $taxCents,
            'discount_cents' => $discountCents,
            'total_cents' => $totalCents,
            'paid_cents' => 0,
            'due_cents' => $totalCents,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'workspace_snapshot' => $workspaceSnapshot,
            'client_snapshot' => $clientSnapshot,
            'created_by_user_id' => Auth::id(),
        ]);

        // Handle customer password protection if checked
        if ($this->isPdfPasswordProtected && !empty($this->pdfPassword)) {
            $this->pdfService->configurePassword($invoice, $this->pdfPassword);
        }

        // Create line items
        foreach ($this->items as $sort => $item) {
            $rateCents = (int) round(((float)$item['rate']) * 100);
            $amountCents = (int) round(((float)$item['amount']) * 100);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => !empty($item['service_id']) ? $item['service_id'] : null,
                'description' => $item['description'],
                'category' => $item['category'] ?? 'General',
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? 'unit',
                'rate_cents' => $rateCents,
                'tax_rate' => $this->taxRate,
                'tax_cents' => (int) round(($amountCents * $this->taxRate) / 100),
                'amount_cents' => $amountCents,
                'sort_order' => $sort,
            ]);
        }

        session()->flash('message', "Invoice {$invoiceNumber} successfully created!");
        return redirect()->route('invoices.show', $invoice->id);
    }

    public function render()
    {
        $clients = Client::orderBy('company_name')->get();
        $services = Service::where('is_active', true)->orderBy('category')->orderBy('name')->get();

        return view('livewire.invoice-builder', [
            'clients' => $clients,
            'services' => $services,
        ]);
    }
}
