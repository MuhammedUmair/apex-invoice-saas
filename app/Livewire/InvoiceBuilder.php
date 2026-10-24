<?php

namespace App\Livewire;

use App\Mail\InvoiceMailable;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Services\InvoiceNumberService;
use App\Services\PdfSecurityService;
use App\Services\TaxCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class InvoiceBuilder extends Component
{
    public ?string $invoiceId = null;
    public bool $isEditing = false;
    public string $currentStatus = 'draft';

    // Walk-in vs Existing Client Toggle
    public bool $isWalkin = false;
    public string $walkinName = 'Walk-in / General Customer';
    public string $walkinEmail = '';
    public string $walkinPhone = '';
    public string $walkinAddress = '';

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

    // Direct Email Dispatch on Publish
    public bool $sendEmailToClient = true;

    // Future Invoice Planning / Scheduling
    public bool $isScheduled = false;
    public string $scheduledPublishDate = '';
    public bool $scheduledSendEmail = true;
    public bool $scheduledAlertVendor = true;

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

    public function mount(?string $invoiceId = null)
    {
        $workspace = Auth::user()->workspace;

        if ($invoiceId) {
            $invoice = Invoice::where('workspace_id', $workspace->id)
                ->with(['client', 'items'])
                ->findOrFail($invoiceId);

            $this->invoiceId = $invoice->id;
            $this->isEditing = true;
            $this->currentStatus = $invoice->status;
            $this->clientId = $invoice->client_id;
            $this->invoiceNumber = $invoice->invoice_number;
            $this->issueDate = $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : date('Y-m-d');
            $this->dueDate = $invoice->due_date ? $invoice->due_date->format('Y-m-d') : date('Y-m-d', strtotime('+14 days'));
            $this->currency = $invoice->currency;
            $this->taxName = $invoice->tax_name;
            $this->taxRate = (float) $invoice->tax_rate;
            $this->discountAmount = $invoice->discount_cents / 100;
            $this->notes = $invoice->notes ?? '';
            $this->terms = $invoice->terms ?? '';
            $this->isPdfPasswordProtected = (bool) $invoice->is_pdf_password_protected;

            // Future planning properties
            $this->isScheduled = (bool) $invoice->is_scheduled;
            $this->scheduledPublishDate = $invoice->scheduled_publish_date ? $invoice->scheduled_publish_date->format('Y-m-d') : '';
            $this->scheduledSendEmail = (bool) $invoice->scheduled_send_email;
            $this->scheduledAlertVendor = (bool) $invoice->scheduled_alert_vendor;

            $client = $invoice->client;
            if ($client) {
                $this->countryTaxCode = $client->country_code ?? 'US';
                if (str_contains(strtolower($client->name), 'walk-in') || str_contains(strtolower($client->name), 'general customer')) {
                    $this->isWalkin = true;
                    $this->walkinName = $client->name;
                    $this->walkinEmail = $client->email ?? '';
                    $this->walkinPhone = $client->phone ?? '';
                }
            }

            $this->items = [];
            foreach ($invoice->items as $item) {
                $this->items[] = [
                    'service_id' => $item->service_id ?? '',
                    'description' => $item->description,
                    'category' => $item->category ?? 'General',
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit ?? 'unit',
                    'rate' => $item->rate_cents / 100,
                    'tax_rate' => (float) $item->tax_rate,
                    'amount' => $item->amount_cents / 100,
                ];
            }

            if (empty($this->items)) {
                $this->addItem();
            }

            $this->recalculate();
            return;
        }

        // New invoice defaults
        $this->issueDate = date('Y-m-d');
        $this->dueDate = date('Y-m-d', strtotime('+14 days'));
        $this->scheduledPublishDate = date('Y-m-d', strtotime('+7 days'));
        $this->currency = $workspace->currency ?? 'USD';
        $this->countryTaxCode = $workspace->country_code ?? 'US';

        $this->applyCountryTaxPreset($this->countryTaxCode);

        // Add 1 default empty item
        $this->addItem();
    }

    /**
     * Automatic live calculation hook triggered whenever any input/property changes.
     */
    public function updated($propertyName = null)
    {
        if ($propertyName === 'countryTaxCode') {
            $this->applyCountryTaxPreset($this->countryTaxCode);
        } elseif ($propertyName === 'clientId' && !empty($this->clientId)) {
            $client = Client::find($this->clientId);
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

    public function enableWalkin()
    {
        $this->isWalkin = true;
        $this->clientId = '';
    }

    public function disableWalkin()
    {
        $this->isWalkin = false;
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

    /**
     * 1-Click Generic Product Presets for fast invoicing.
     */
    public function addPresetItem(string $type)
    {
        $presets = [
            'consulting' => ['name' => 'Consulting & Professional Advisory', 'rate' => 100.00, 'unit' => 'hr', 'category' => 'Consulting'],
            'web_dev' => ['name' => 'Web Design & Software Development', 'rate' => 750.00, 'unit' => 'project', 'category' => 'Development'],
            'retainer' => ['name' => 'Monthly Retainer & Technical Support', 'rate' => 500.00, 'unit' => 'month', 'category' => 'Retainer'],
            'labor' => ['name' => 'Custom Technical Labor / Implementation', 'rate' => 75.00, 'unit' => 'hr', 'category' => 'Labor'],
            'marketing' => ['name' => 'SEO Optimization & Digital Marketing', 'rate' => 250.00, 'unit' => 'package', 'category' => 'Marketing'],
            'product' => ['name' => 'General Merchandise / Goods Supply', 'rate' => 45.00, 'unit' => 'item', 'category' => 'Product'],
        ];

        if (isset($presets[$type])) {
            $p = $presets[$type];
            $this->items[] = [
                'service_id' => '',
                'description' => $p['name'],
                'category' => $p['category'],
                'quantity' => 1.0,
                'unit' => $p['unit'],
                'rate' => (float) $p['rate'],
                'tax_rate' => $this->taxRate,
                'amount' => (float) $p['rate'],
            ];
            $this->recalculate();
        }
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
        // Validation rules
        $rules = [
            'issueDate' => 'required|date',
            'dueDate' => 'required|date|after_or_equal:issueDate',
            'currency' => 'required|string|size:3',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
        ];

        if ($this->isWalkin) {
            $rules['walkinName'] = 'required|string|max:255';
            $rules['walkinEmail'] = 'nullable|email|max:255';
        } else {
            $rules['clientId'] = 'required|exists:clients,id';
        }

        if ($this->isScheduled) {
            $rules['scheduledPublishDate'] = 'required|date|after_or_equal:today';
        }

        $this->validate($rules);

        $workspace = Auth::user()->workspace;

        // Resolve or create Client
        if ($this->isWalkin) {
            $client = Client::firstOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'name' => $this->walkinName ?: 'Walk-in Customer',
                    'email' => $this->walkinEmail ?: 'walkin_' . time() . '@redmapleagency.com',
                ],
                [
                    'phone' => $this->walkinPhone,
                    'address' => $this->walkinAddress,
                    'country_code' => $this->countryTaxCode ?: 'US',
                    'preferred_currency' => $this->currency ?: 'USD',
                ]
            );
            $this->clientId = $client->id;
        } else {
            $client = Client::findOrFail($this->clientId);
        }

        $subtotalCents = (int) round($this->subtotal * 100);
        $discountCents = (int) round($this->discountAmount * 100);
        $taxCents = (int) round($this->taxAmount * 100);
        $totalCents = (int) round($this->totalAmount * 100);

        // Historical snapshots for GAAP compliance
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

        // If scheduled, invoice starts as draft with schedule flags
        $effectiveStatus = $this->isScheduled ? 'draft' : $status;

        if ($this->isEditing && $this->invoiceId) {
            $invoice = Invoice::where('workspace_id', $workspace->id)->findOrFail($this->invoiceId);
            $paidCents = $invoice->paid_cents ?? 0;

            $invoice->update([
                'client_id' => $client->id,
                'status' => $effectiveStatus,
                'issue_date' => $this->issueDate,
                'due_date' => $this->dueDate,
                'currency' => $this->currency,
                'subtotal_cents' => $subtotalCents,
                'tax_name' => $this->taxName,
                'tax_rate' => $this->taxRate,
                'tax_cents' => $taxCents,
                'discount_cents' => $discountCents,
                'total_cents' => $totalCents,
                'due_cents' => max(0, $totalCents - $paidCents),
                'notes' => $this->notes,
                'terms' => $this->terms,
                'client_snapshot' => $clientSnapshot,
                'workspace_snapshot' => $workspaceSnapshot,
                'is_scheduled' => $this->isScheduled,
                'scheduled_publish_date' => $this->isScheduled ? $this->scheduledPublishDate : null,
                'scheduled_send_email' => $this->scheduledSendEmail,
                'scheduled_alert_vendor' => $this->scheduledAlertVendor,
            ]);

            // Replace line items
            $invoice->items()->delete();
        } else {
            $invoiceNumber = $this->numberService->generateNextNumber($workspace);

            $invoice = Invoice::create([
                'workspace_id' => $workspace->id,
                'client_id' => $client->id,
                'invoice_number' => $invoiceNumber,
                'status' => $effectiveStatus,
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
                'is_scheduled' => $this->isScheduled,
                'scheduled_publish_date' => $this->isScheduled ? $this->scheduledPublishDate : null,
                'scheduled_send_email' => $this->scheduledSendEmail,
                'scheduled_alert_vendor' => $this->scheduledAlertVendor,
            ]);
        }

        // PDF Security
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

        // Option 13: Direct Email Dispatch to registered client on publish
        if ($status !== 'draft' && !$this->isScheduled && $this->sendEmailToClient && !empty($client->email)) {
            try {
                Mail::to($client->email)->send(new InvoiceMailable($invoice));
                $invoice->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to email invoice {$invoice->id}: " . $e->getMessage());
            }
        }

        $actionText = $this->isEditing ? 'revised & updated' : ($this->isScheduled ? 'scheduled for future publishing' : 'successfully created');
        session()->flash('message', "Invoice {$invoice->invoice_number} {$actionText}!");

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
