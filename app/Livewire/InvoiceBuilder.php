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
    public string $scheduledPublishTime = '09:00';
    public bool $scheduledSendEmail = true;
    public bool $scheduledAlertVendor = true;

    // Late Payment Surcharge Option
    public bool $hasLateSurcharge = false;
    public string $lateSurchargeType = 'percent';
    public float $lateSurchargeValue = 5.00;
    public int $lateSurchargeDays = 7;

    // Recurring Billing Options (Mockup Feature)
    public bool $isRecurring = true;
    public int $recurringInterval = 1;
    public string $recurringFrequency = 'month';
    public string $recurringNextDate = '';
    public bool $recurringAutoEmail = true;
    public bool $recurringVendorAlert = true;

    public array $items = [];

    // Summary calculations (formatted)
    public float $subtotal = 0.00;
    public float $taxAmount = 0.00;
    public float $totalAmount = 0.00;

    // Promotional Coupon Properties (Items 4 & 5)
    public string $couponCode = '';
    public ?string $appliedCouponCode = null;
    public ?string $couponMessage = null;
    public bool $couponApplied = false;

    protected TaxCalculationService $taxService;
    protected InvoiceNumberService $numberService;
    protected PdfSecurityService $pdfService;

    protected array $currencyToTaxMap = [
        'PKR' => 'PK',
        'USD' => 'US',
        'AED' => 'AE',
        'SAR' => 'SA',
        'GBP' => 'GB',
        'EUR' => 'DE',
        'CAD' => 'CA',
        'AUD' => 'AU',
        'INR' => 'IN',
        'TRY' => 'TR',
    ];

    protected array $taxToCurrencyMap = [
        'PK' => 'PKR',
        'AE' => 'AED',
        'SA' => 'SAR',
        'GB' => 'GBP',
        'US' => 'USD',
        'DE' => 'EUR',
        'FR' => 'EUR',
        'ES' => 'EUR',
        'CA' => 'CAD',
        'AU' => 'AUD',
        'IN' => 'INR',
        'TR' => 'TRY',
    ];

    public array $presets = [
        'consulting' => [
            'name' => 'Consulting & Professional Advisory',
            'rate' => 100.00,
            'unit' => 'Hour',
            'category' => 'Consulting',
        ],
        'web_dev' => [
            'name' => 'Web Design & Software Development',
            'rate' => 750.00,
            'unit' => 'Project',
            'category' => 'Development',
        ],
        'retainer' => [
            'name' => 'Monthly Retainer & Technical Support',
            'rate' => 500.00,
            'unit' => 'Month',
            'category' => 'Retainer',
        ],
        'labor' => [
            'name' => 'Custom Technical Labor / Implementation',
            'rate' => 75.00,
            'unit' => 'Hour',
            'category' => 'Labor',
        ],
        'marketing' => [
            'name' => 'SEO Optimization & Digital Marketing',
            'rate' => 250.00,
            'unit' => 'Package',
            'category' => 'Marketing',
        ],
        'product' => [
            'name' => 'General Merchandise / Goods Supply',
            'rate' => 45.00,
            'unit' => 'Item',
            'category' => 'Product',
        ],
    ];

    public function boot(
        TaxCalculationService $taxService,
        InvoiceNumberService $numberService,
        PdfSecurityService $pdfService
    ) {
        $this->taxService = $taxService;
        $this->numberService = $numberService;
        $this->pdfService = $pdfService;

        // Apply active session language
        app()->setLocale(session('locale', config('app.locale', 'en')));
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

            // Late Surcharge
            $this->hasLateSurcharge = (bool) $invoice->has_late_surcharge;
            $this->lateSurchargeType = $invoice->late_surcharge_type ?: 'percent';
            $this->lateSurchargeValue = (float) ($invoice->late_surcharge_value ?: 5.00);
            $this->lateSurchargeDays = (int) ($invoice->late_surcharge_days ?: 7);

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
        $this->recurringNextDate = date('Y-m-d', strtotime('+1 month'));
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
        if ($propertyName === 'currency') {
            $this->onCurrencyChange($this->currency);
        } elseif ($propertyName === 'countryTaxCode') {
            $this->onCountryTaxChange($this->countryTaxCode);
        } elseif ($propertyName === 'clientId') {
            if ($this->clientId === 'walkin') {
                $this->isWalkin = true;
            } else {
                $this->isWalkin = false;
                if (!empty($this->clientId)) {
                    $client = Client::find($this->clientId);
                    if ($client) {
                        if ($client->preferred_currency) {
                            $this->onCurrencyChange($client->preferred_currency);
                        }
                        if ($client->country_code) {
                            $this->onCountryTaxChange($client->country_code);
                        }
                    }
                }
            }
        } elseif ($propertyName === 'issueDate') {
            $this->updatedIssueDate($this->issueDate);
        } elseif ($propertyName === 'dueDate') {
            $this->updatedDueDate($this->dueDate);
        }

        $this->recalculate();
    }

    public function updatedCurrency($val)
    {
        $this->onCurrencyChange($val);
    }

    public function onCurrencyChange($val)
    {
        $this->currency = $val;
        if (isset($this->currencyToTaxMap[$val])) {
            $this->countryTaxCode = $this->currencyToTaxMap[$val];
            $this->applyCountryTaxPreset($this->countryTaxCode);
        }
        $this->recalculate();
    }

    public function updatedCountryTaxCode($val)
    {
        $this->onCountryTaxChange($val);
    }

    public function onCountryTaxChange($val)
    {
        $this->countryTaxCode = $val;
        $this->applyCountryTaxPreset($val);
        if (isset($this->taxToCurrencyMap[$val])) {
            $this->currency = $this->taxToCurrencyMap[$val];
        }
        $this->recalculate();
    }

    public function updatedIssueDate($val)
    {
        // Item 16: Automatically mark as planned future invoice if issue date > today
        if ($val && strtotime($val) > strtotime(date('Y-m-d'))) {
            $this->isScheduled = true;
            $this->scheduledPublishDate = $val;
        }

        // Item 17: Payment due date cannot be older than issue date
        if ($val && $this->dueDate && strtotime($this->dueDate) < strtotime($val)) {
            $this->dueDate = date('Y-m-d', strtotime($val . ' +14 days'));
        }
    }

    public function updatedDueDate($val)
    {
        // Item 17: Payment due date cannot be older than issue date
        if ($val && $this->issueDate && strtotime($val) < strtotime($this->issueDate)) {
            $this->dueDate = $this->issueDate;
            $this->addError('dueDate', 'Due date cannot be earlier than invoice issue date.');
        }
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

    public function toggleRecurring()
    {
        $this->isRecurring = !$this->isRecurring;
        if ($this->isRecurring && empty($this->recurringNextDate)) {
            $this->recurringNextDate = date('Y-m-d', strtotime('+1 month'));
        }
    }

    public function applyCountryTaxPreset(string $countryCode)
    {
        $preset = $this->taxService->getPresetForCountry($countryCode);
        $this->taxName = $preset['name'];
        $this->taxRate = (float) $preset['rate'];

        // Synchronize taxRate across all line items
        foreach ($this->items as $i => $item) {
            $this->items[$i]['tax_rate'] = $this->taxRate;
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'service_id' => '',
            'description' => '',
            'category' => 'General',
            'quantity' => 1,
            'unit' => 'Project',
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
        if (!isset($this->presets[$type])) {
            return;
        }

        $p = $this->presets[$type];
        $presetKey = 'preset_' . $type;

        // Check if an item with this preset is already in the list
        $existingIndex = null;
        foreach ($this->items as $i => $item) {
            if (($item['service_id'] ?? '') === $presetKey) {
                $existingIndex = $i;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Click multiple times -> increment quantity!
            $currentQty = max(1, (int) ($this->items[$existingIndex]['quantity'] ?? 1));
            $newQty = $currentQty + 1;
            $this->items[$existingIndex]['quantity'] = $newQty;
            $this->items[$existingIndex]['amount'] = round($newQty * (float) $this->items[$existingIndex]['rate'], 2);
        } else {
            $newItem = [
                'service_id' => $presetKey,
                'description' => '', // Empty by default as requested!
                'category' => $p['category'],
                'quantity' => 1,
                'unit' => $p['unit'], // Unit updated based on quick preset!
                'rate' => (float) $p['rate'],
                'tax_rate' => $this->taxRate,
                'amount' => (float) $p['rate'],
            ];

            // Overwrite first empty service row if empty or unselected
            if (count($this->items) === 1 && empty($this->items[0]['service_id']) && empty($this->items[0]['rate'])) {
                $this->items[0] = $newItem;
            } else {
                $this->items[] = $newItem;
            }
        }

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
        if (!isset($this->items[$index])) {
            return;
        }

        if (empty($serviceId)) {
            $this->items[$index]['service_id'] = '';
            $this->items[$index]['description'] = '';
            $this->items[$index]['unit'] = 'Project';
            $this->items[$index]['rate'] = 0.00;
            $this->items[$index]['amount'] = 0.00;
            $this->recalculate();
            return;
        }

        // Check if selected a preset
        if (str_starts_with($serviceId, 'preset_')) {
            $type = substr($serviceId, 7);
            if (isset($this->presets[$type])) {
                $p = $this->presets[$type];
                $this->items[$index]['service_id'] = $serviceId;
                $this->items[$index]['description'] = ''; // Empty by default as requested!
                $this->items[$index]['category'] = $p['category'];
                $this->items[$index]['unit'] = $p['unit']; // Unit updated based on preset!
                $this->items[$index]['rate'] = (float) $p['rate'];
                $qty = max(1, (int) ($this->items[$index]['quantity'] ?? 1));
                $this->items[$index]['amount'] = round($qty * (float) $p['rate'], 2);
                $this->recalculate();
                return;
            }
        }

        // Otherwise catalog Service model
        $service = Service::find($serviceId);
        if ($service) {
            $this->items[$index]['service_id'] = $service->id;
            $this->items[$index]['description'] = ''; // Empty by default as requested!
            $this->items[$index]['category'] = $service->category ?? 'General';
            $this->items[$index]['unit'] = $this->normalizeUnit($service->unit); // Unit updated based on service select!
            $this->items[$index]['rate'] = (float) ($service->rate_cents / 100);
            $qty = max(1, (int) ($this->items[$index]['quantity'] ?? 1));
            $this->items[$index]['amount'] = round($qty * (float) ($service->rate_cents / 100), 2);
            $this->recalculate();
        }
    }

    protected function normalizeUnit(?string $unit): string
    {
        if (!$unit) return 'Project';
        $u = strtolower(trim($unit));
        return match($u) {
            'hr', 'hour', 'hours' => 'Hour',
            'mo', 'month', 'months' => 'Month',
            'day', 'days' => 'Day',
            'item', 'items' => 'Item',
            'pkg', 'package', 'packages' => 'Package',
            'unit', 'units' => 'Unit',
            'yr', 'year', 'years' => 'Year',
            'project', 'projects' => 'Project',
            default => ucfirst($unit),
        };
    }

    public function applyCoupon()
    {
        if (empty(trim($this->couponCode))) {
            $this->addError('couponCode', 'Please enter a coupon code.');
            return;
        }

        $couponService = app(\App\Services\CouponService::class);
        $amountCents = (int) round($this->subtotal * 100);
        $check = $couponService->validate($this->couponCode, 'invoice', $amountCents, $this->currency);

        if (!$check['valid']) {
            $this->couponApplied = false;
            $this->couponMessage = null;
            $this->addError('couponCode', $check['message']);
            return;
        }

        $coupon = $check['coupon'];
        $this->resetErrorBag('couponCode');
        $this->appliedCouponCode = $coupon->code;
        $this->couponApplied = true;

        if ($coupon->offer_type === 'service' || !empty($coupon->service_name)) {
            $serviceName = $coupon->service_name ?: 'Complimentary Promotional Service';
            $this->items[] = [
                'service_id' => $coupon->service_id ?: 'preset_promo',
                'description' => "[Promo Offer: {$coupon->code}] " . $serviceName,
                'category' => 'Promotion',
                'quantity' => 1,
                'unit' => 'Package',
                'rate' => 0.00,
                'tax_rate' => 0,
                'amount' => 0.00,
            ];
            $this->couponMessage = "Complimentary service '{$serviceName}' added to invoice at no charge!";
        } else {
            $discountCents = $coupon->calculateDiscountCents($amountCents);
            $this->discountAmount = round($discountCents / 100, 2);
            $this->couponMessage = "Promo code '{$coupon->code}' applied: {$this->currency} " . number_format($this->discountAmount, 2) . " discount.";
        }

        $this->recalculate();
    }

    public function removeCoupon()
    {
        $this->couponCode = '';
        $this->appliedCouponCode = null;
        $this->couponMessage = null;
        $this->couponApplied = false;
        $this->discountAmount = 0.00;

        // Remove any promo items
        $this->items = array_values(array_filter($this->items, function ($item) {
            return ($item['category'] ?? '') !== 'Promotion';
        }));

        if (empty($this->items)) {
            $this->addItem();
        }

        $this->recalculate();
    }

    public function recalculate()
    {
        $subtotalCents = 0;

        foreach ($this->items as $i => $item) {
            $qty = max(1, (int) round((float) ($item['quantity'] ?? 1)));
            $this->items[$i]['quantity'] = $qty;
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
            'items.*.quantity' => 'required|integer|min:1',
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
                'has_late_surcharge' => $this->hasLateSurcharge,
                'late_surcharge_type' => $this->lateSurchargeType,
                'late_surcharge_value' => $this->lateSurchargeValue,
                'late_surcharge_days' => $this->lateSurchargeDays,
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
                'has_late_surcharge' => $this->hasLateSurcharge,
                'late_surcharge_type' => $this->lateSurchargeType,
                'late_surcharge_value' => $this->lateSurchargeValue,
                'late_surcharge_days' => $this->lateSurchargeDays,
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

            $desc = trim($item['description'] ?? '');
            if (empty($desc)) {
                if (!empty($item['service_id'])) {
                    if (str_starts_with($item['service_id'], 'preset_')) {
                        $type = substr($item['service_id'], 7);
                        $desc = $this->presets[$type]['name'] ?? 'Professional Service';
                    } else {
                        $srv = Service::find($item['service_id']);
                        $desc = $srv ? $srv->name : 'Professional Service';
                    }
                } else {
                    $desc = 'Professional Service';
                }
            }

            $serviceId = (!empty($item['service_id']) && !str_starts_with($item['service_id'], 'preset_'))
                ? $item['service_id']
                : null;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $serviceId,
                'description' => $desc,
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

        // Create recurring retainer if enabled
        if ($this->isRecurring && $client) {
            try {
                \App\Models\RecurringInvoice::create([
                    'workspace_id' => $workspace->id,
                    'client_id' => $client->id,
                    'title' => 'Recurring Retainer for ' . ($client->company_name ?: $client->name),
                    'currency' => $this->currency,
                    'amount_cents' => $totalCents,
                    'frequency' => $this->recurringFrequency === 'month' ? 'monthly' : ($this->recurringFrequency === 'year' ? 'yearly' : 'monthly'),
                    'day_of_month' => (int) date('j', strtotime($this->issueDate ?: 'now')),
                    'require_vendor_approval' => $this->recurringVendorAlert,
                    'auto_send_email' => $this->recurringAutoEmail,
                    'items' => $this->items,
                    'notes' => $this->notes,
                    'next_run_date' => $this->recurringNextDate ?: date('Y-m-d', strtotime('+1 month')),
                    'status' => 'active',
                ]);
            } catch (\Throwable $e) {
                Log::warning("Could not auto-create recurring invoice: " . $e->getMessage());
            }
        }

        $actionText = $this->isEditing ? 'revised & updated' : ($this->isScheduled ? 'scheduled for future publishing' : 'successfully created');
        session()->flash('message', "Invoice {$invoice->invoice_number} {$actionText}!");

        return redirect()->route('invoices.show', $invoice->id);
    }

    public function getSelectedClientProperty(): ?Client
    {
        return $this->clientId ? Client::find($this->clientId) : null;
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
