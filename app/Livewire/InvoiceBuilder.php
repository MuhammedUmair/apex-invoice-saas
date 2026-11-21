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
    public string $walkinProvince = 'ON';

    public ?string $clientId = '';
    public string $invoiceNumber = '';
    public string $issueDate = '';
    public string $dueDate = '';
    public string $currency = 'USD';
    public string $language = 'en';
    public string $countryTaxCode = 'US';
    public string $taxName = 'VAT';
    public float $taxRate = 0.00;
    public string $federalTaxName = 'GST';
    public float $federalTaxRate = 0.00;
    public float $federalTaxAmount = 0.00;
    public ?string $provincialTaxName = null;
    public float $provincialTaxRate = 0.00;
    public float $provincialTaxAmount = 0.00;
    public bool $isDualTax = false;
    public ?string $taxCalculationNote = null;
    public float $discountAmount = 0.00;
    public string $notes = '';
    public string $terms = 'Payment is due within the specified payment terms. Please remit payment via the portal link or wire details below.';
    public bool $isPdfPasswordProtected = false;
    public string $pdfPassword = '';

    // Direct Email Dispatch on Publish (Default: false to prevent accidental dispatches)
    public bool $sendEmailToClient = false;

    // Industry Filter for Service Presets (Request 8)
    public string $presetIndustryFilter = 'all';

    // Interactive Pre-Dispatch Review Modal (Item 3)
    public bool $showReviewModal = false;

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
        // Generic / Core
        'consulting' => [
            'name' => 'Consulting & Professional Advisory',
            'rate' => 100.00,
            'unit' => 'Hour',
            'category' => 'Consulting',
            'industry' => 'general',
        ],
        'web_dev' => [
            'name' => 'Web Design & Software Development',
            'rate' => 750.00,
            'unit' => 'Project',
            'category' => 'Development',
            'industry' => 'general',
        ],
        'labor' => [
            'name' => 'Custom Technical Labor / Implementation',
            'rate' => 75.00,
            'unit' => 'Hour',
            'category' => 'Labor',
            'industry' => 'general',
        ],

        // 1. Computer Shop
        'computer_diagnostics' => [
            'name' => 'Computer & Laptop Hardware Diagnostics',
            'rate' => 50.00,
            'unit' => 'Diagnosis',
            'category' => 'Diagnostics',
            'industry' => 'computer_shop',
        ],
        'computer_laptop_repair' => [
            'name' => 'Laptop Motherboard & Display Component Repair',
            'rate' => 120.00,
            'unit' => 'Service',
            'category' => 'Repair',
            'industry' => 'computer_shop',
        ],
        'computer_os_install' => [
            'name' => 'Clean Windows / Linux OS Installation & Drivers',
            'rate' => 65.00,
            'unit' => 'Setup',
            'category' => 'Software',
            'industry' => 'computer_shop',
        ],
        'computer_ram_ssd' => [
            'name' => 'High-Speed NVMe SSD & RAM Memory Upgrade',
            'rate' => 110.00,
            'unit' => 'Upgrade',
            'category' => 'Hardware',
            'industry' => 'computer_shop',
        ],
        'computer_virus_clean' => [
            'name' => 'Deep Malware, Trojan & Ransomware Clean',
            'rate' => 75.00,
            'unit' => 'Service',
            'category' => 'Security',
            'industry' => 'computer_shop',
        ],
        'computer_data_recovery' => [
            'name' => 'Storage Data Recovery & Secure Backup Setup',
            'rate' => 160.00,
            'unit' => 'Drive',
            'category' => 'Data',
            'industry' => 'computer_shop',
        ],
        'computer_custom_build' => [
            'name' => 'Custom Gaming & Deep Learning Workstation Build',
            'rate' => 180.00,
            'unit' => 'Unit',
            'category' => 'Assembly',
            'industry' => 'computer_shop',
        ],

        // 2. Transportation
        'trans_ftl' => [
            'name' => 'Full Truckload (FTL) Regional Freight Haul',
            'rate' => 1250.00,
            'unit' => 'Trip',
            'category' => 'Freight',
            'industry' => 'transportation',
        ],
        'trans_ltl' => [
            'name' => 'Less-Than-Truckload (LTL) Pallet Cargo Dispatch',
            'rate' => 320.00,
            'unit' => 'Pallet',
            'category' => 'Cargo',
            'industry' => 'transportation',
        ],
        'trans_intercity' => [
            'name' => 'Inter-City Dedicated Courier Dispatch',
            'rate' => 480.00,
            'unit' => 'Route',
            'category' => 'Courier',
            'industry' => 'transportation',
        ],
        'trans_cold_chain' => [
            'name' => 'Temperature-Controlled Cold-Chain Haulage',
            'rate' => 1400.00,
            'unit' => 'Shipment',
            'category' => 'Refrigerated',
            'industry' => 'transportation',
        ],
        'trans_port_drayage' => [
            'name' => 'Intermodal Port Container Drayage Transfer',
            'rate' => 550.00,
            'unit' => 'Container',
            'category' => 'Intermodal',
            'industry' => 'transportation',
        ],
        'trans_last_mile' => [
            'name' => 'Same-Day Urgent Last-Mile Route Delivery',
            'rate' => 95.00,
            'unit' => 'Route',
            'category' => 'Last Mile',
            'industry' => 'transportation',
        ],

        // 3. Limousine Taxi
        'limo_airport_vip' => [
            'name' => 'VIP Airport Chauffeur & Meet-and-Greet',
            'rate' => 140.00,
            'unit' => 'Transfer',
            'category' => 'Airport',
            'industry' => 'limousine_taxi',
        ],
        'limo_hourly_chauffeur' => [
            'name' => 'Hourly Executive Luxury Sedan Chauffeur',
            'rate' => 85.00,
            'unit' => 'Hour',
            'category' => 'Hourly',
            'industry' => 'limousine_taxi',
        ],
        'limo_full_day_hire' => [
            'name' => 'Full-Day Premium Black Car Chauffeur Service',
            'rate' => 650.00,
            'unit' => 'Day',
            'category' => 'Day Hire',
            'industry' => 'limousine_taxi',
        ],
        'limo_wedding_escort' => [
            'name' => 'Presidential Stretch Limousine Wedding Escort',
            'rate' => 550.00,
            'unit' => 'Event',
            'category' => 'Events',
            'industry' => 'limousine_taxi',
        ],
        'limo_corporate_delegation' => [
            'name' => 'Corporate Executive Delegation Van Shuttle',
            'rate' => 380.00,
            'unit' => 'Service',
            'category' => 'Corporate',
            'industry' => 'limousine_taxi',
        ],
        'limo_point_to_point' => [
            'name' => 'Point-to-Point City Luxury Transfer',
            'rate' => 110.00,
            'unit' => 'Trip',
            'category' => 'Transfer',
            'industry' => 'limousine_taxi',
        ],

        // 4. Industrial
        'ind_preventive_maint' => [
            'name' => 'Heavy Machinery Preventive Maintenance',
            'rate' => 650.00,
            'unit' => 'Inspection',
            'category' => 'Maintenance',
            'industry' => 'industrial',
        ],
        'ind_cnc_precision' => [
            'name' => 'Precision CNC Milling & Lathe Machining',
            'rate' => 135.00,
            'unit' => 'Hour',
            'category' => 'Machining',
            'industry' => 'industrial',
        ],
        'ind_pipe_welding' => [
            'name' => 'Certified High-Pressure Pipe TIG/MIG Welding',
            'rate' => 95.00,
            'unit' => 'Hour',
            'category' => 'Welding',
            'industry' => 'industrial',
        ],
        'ind_panel_assembly' => [
            'name' => 'Industrial Electrical PLC & Control Panel Assembly',
            'rate' => 1850.00,
            'unit' => 'Unit',
            'category' => 'Electrical',
            'industry' => 'industrial',
        ],
        'ind_hydraulic_overhaul' => [
            'name' => 'Hydraulic Cylinder & Pump Seal Overhaul',
            'rate' => 780.00,
            'unit' => 'Service',
            'category' => 'Hydraulics',
            'industry' => 'industrial',
        ],
        'ind_safety_audit' => [
            'name' => 'Industrial Workplace & OSHA Plant Safety Audit',
            'rate' => 1200.00,
            'unit' => 'Audit',
            'category' => 'Safety',
            'industry' => 'industrial',
        ],

        // 5. Construction
        'const_foundation' => [
            'name' => 'Concrete Foundation & Footing Pouring',
            'rate' => 3200.00,
            'unit' => 'Project',
            'category' => 'Foundation',
            'industry' => 'construction',
        ],
        'const_steel_framing' => [
            'name' => 'Structural Steel Beam & Truss Erection',
            'rate' => 2100.00,
            'unit' => 'Ton',
            'category' => 'Framing',
            'industry' => 'construction',
        ],
        'const_drywall' => [
            'name' => 'Drywall Boarding, Taping & Skim Plastering',
            'rate' => 45.00,
            'unit' => 'Sq Meter',
            'category' => 'Finishing',
            'industry' => 'construction',
        ],
        'const_flooring' => [
            'name' => 'Commercial Epoxy / Ceramic Tile Installation',
            'rate' => 60.00,
            'unit' => 'Sq Meter',
            'category' => 'Flooring',
            'industry' => 'construction',
        ],
        'const_rough_plumbing' => [
            'name' => 'Rough-in Commercial Plumbing & Drainage',
            'rate' => 1450.00,
            'unit' => 'Phase',
            'category' => 'Plumbing',
            'industry' => 'construction',
        ],
        'const_electrical_wiring' => [
            'name' => 'Main Electrical Panel & Conduit Wiring',
            'rate' => 1600.00,
            'unit' => 'Phase',
            'category' => 'Electrical',
            'industry' => 'construction',
        ],
        'const_site_excavation' => [
            'name' => 'Heavy Site Grading, Trenching & Earthmoving',
            'rate' => 175.00,
            'unit' => 'Hour',
            'category' => 'Excavation',
            'industry' => 'construction',
        ],

        // 6. Distributor
        'dist_pallet_supply' => [
            'name' => 'Wholesale Bulk Pallet Distribution',
            'rate' => 850.00,
            'unit' => 'Pallet',
            'category' => 'Wholesale',
            'industry' => 'distributor',
        ],
        'dist_fmcg_pack' => [
            'name' => 'Fast-Moving Consumer Goods (FMCG) Case Pack',
            'rate' => 1950.00,
            'unit' => 'Lot',
            'category' => 'FMCG',
            'industry' => 'distributor',
        ],
        'dist_warehousing' => [
            'name' => 'Secure Pallet Racking & Warehousing',
            'rate' => 55.00,
            'unit' => 'Month',
            'category' => 'Storage',
            'industry' => 'distributor',
        ],
        'dist_cross_dock' => [
            'name' => 'Cross-Dock Logistics & Sorting Transfer',
            'rate' => 240.00,
            'unit' => 'Inbound',
            'category' => 'Cross-Dock',
            'industry' => 'distributor',
        ],
        'dist_repackaging' => [
            'name' => 'Automated SKU Barcode Label & Repack',
            'rate' => 0.50,
            'unit' => 'Unit',
            'category' => 'Packaging',
            'industry' => 'distributor',
        ],
        'dist_route_delivery' => [
            'name' => 'Scheduled Weekly Supermarket Route Delivery',
            'rate' => 750.00,
            'unit' => 'Store',
            'category' => 'Distribution',
            'industry' => 'distributor',
        ],

        // 7. Marketing Agency
        'mkt_brand_strategy' => [
            'name' => 'Full Brand Identity, Guidelines & Logo Suite',
            'rate' => 1400.00,
            'unit' => 'Package',
            'category' => 'Branding',
            'industry' => 'marketing_agency',
        ],
        'mkt_paid_ads' => [
            'name' => 'Multi-Platform Paid Search & Social Ad Management',
            'rate' => 1100.00,
            'unit' => 'Month',
            'category' => 'Paid Media',
            'industry' => 'marketing_agency',
        ],
        'mkt_seo_retainer' => [
            'name' => 'Technical On-Page & Off-Page SEO Retainer',
            'rate' => 850.00,
            'unit' => 'Month',
            'category' => 'SEO',
            'industry' => 'marketing_agency',
        ],
        'mkt_social_content' => [
            'name' => 'Monthly High-Impact Social Content & Reels',
            'rate' => 750.00,
            'unit' => 'Month',
            'category' => 'Social',
            'industry' => 'marketing_agency',
        ],
        'mkt_landing_page' => [
            'name' => 'Conversion-Optimized Sales Landing Page',
            'rate' => 950.00,
            'unit' => 'Page',
            'category' => 'Web Design',
            'industry' => 'marketing_agency',
        ],
        'mkt_video_production' => [
            'name' => 'Commercial Promotional Video Shoot & Edit',
            'rate' => 1650.00,
            'unit' => 'Video',
            'category' => 'Production',
            'industry' => 'marketing_agency',
        ],
        'mkt_email_funnel' => [
            'name' => 'Automated Email Welcome & Retention Funnels',
            'rate' => 650.00,
            'unit' => 'Funnel',
            'category' => 'Automation',
            'industry' => 'marketing_agency',
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
        $this->presetIndustryFilter = $workspace->business_category ?? 'all';
        $wsCountry = strtoupper($workspace->country_code ?? 'US');
        $this->walkinProvince = $wsCountry === 'CA' ? ($workspace->province ?? $workspace->state ?? 'ON') : '';

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
            $this->language = $invoice->language ?: (session('locale') ?: ($workspace->language ?: 'en'));
            $this->taxName = $invoice->tax_name;
            $this->taxRate = (float) $invoice->tax_rate;
            $this->federalTaxName = $invoice->federal_tax_name ?? $invoice->tax_name;
            $this->federalTaxRate = (float) ($invoice->federal_tax_rate ?? $invoice->tax_rate);
            $this->federalTaxAmount = ($invoice->federal_tax_cents ?? 0) / 100;
            $this->provincialTaxName = $invoice->provincial_tax_name;
            $this->provincialTaxRate = (float) ($invoice->provincial_tax_rate ?? 0);
            $this->provincialTaxAmount = ($invoice->provincial_tax_cents ?? 0) / 100;
            $this->isDualTax = (bool) ($invoice->is_dual_tax ?? false);
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
                $this->countryTaxCode = $client->country_code ?? $wsCountry;
                if (str_contains(strtolower($client->name), 'walk-in') || str_contains(strtolower($client->name), 'general customer')) {
                    $this->isWalkin = true;
                    $this->walkinName = $client->name;
                    $this->walkinEmail = $client->email ?? '';
                    $this->walkinPhone = $client->phone ?? '';
                    $this->walkinProvince = $client->province ?? $client->state ?? ($workspace->province ?? 'ON');
                }
            } else {
                $this->countryTaxCode = $wsCountry;
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

        // New invoice defaults initialized directly from workspace regional settings
        $this->issueDate = date('Y-m-d');
        $this->dueDate = date('Y-m-d', strtotime('+14 days'));
        $this->scheduledPublishDate = date('Y-m-d', strtotime('+7 days'));
        $this->recurringNextDate = date('Y-m-d', strtotime('+1 month'));

        $this->countryTaxCode = $wsCountry;
        $this->currency = $workspace->currency ?? ($wsCountry === 'AE' ? 'AED' : ($wsCountry === 'CA' ? 'CAD' : 'USD'));
        $this->language = session('locale') ?: ($workspace->language ?: 'en');

        $this->applyCountryTaxPreset($this->countryTaxCode);

        if ($workspace->default_tax_rate !== null && (float)$workspace->default_tax_rate > 0) {
            $this->taxRate = (float) $workspace->default_tax_rate;
        }

        // Add 1 default empty item
        $this->addItem();
        $this->recalculate();
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
            $workspace = Auth::user()?->workspace;
            $wsCountry = strtoupper($workspace->country_code ?? 'US');

            if ($this->clientId === 'walkin') {
                $this->isWalkin = true;
                if ($wsCountry === 'CA') {
                    $this->walkinProvince = $this->walkinProvince ?: ($workspace->province ?? $workspace->state ?? 'ON');
                    $this->countryTaxCode = 'CA';
                    $this->applyCountryTaxPreset('CA');
                } else {
                    $this->walkinProvince = '';
                    $this->countryTaxCode = $wsCountry;
                    $this->applyCountryTaxPreset($wsCountry);
                    if ($workspace->default_tax_rate !== null && (float)$workspace->default_tax_rate > 0) {
                        $this->taxRate = (float) $workspace->default_tax_rate;
                    }
                }
            } else {
                $this->isWalkin = false;
                if (!empty($this->clientId)) {
                    $client = Client::find($this->clientId);
                    if ($client) {
                        if ($client->preferred_currency) {
                            $this->currency = $client->preferred_currency;
                        }
                        if ($client->preferred_language) {
                            $this->language = $client->preferred_language;
                        }
                        $clCountry = strtoupper($client->country_code ?? $wsCountry);

                        if ($wsCountry === 'CA') {
                            if ($clCountry !== 'CA') {
                                // Canadian workspace billing international client (e.g. AE, US, GB)
                                $this->countryTaxCode = 'EXPORT';
                                $this->taxName = 'Zero-Rated Export';
                                $this->taxRate = 0.00;
                                foreach ($this->items as $i => $item) {
                                    $this->items[$i]['tax_rate'] = 0.00;
                                }
                            } else {
                                // Canadian domestic client
                                $this->countryTaxCode = 'CA';
                                $this->applyCountryTaxPreset('CA');
                            }
                        } else {
                            // Non-Canadian workspace (e.g. UAE)
                            if ($clCountry === $wsCountry) {
                                // Domestic client (e.g. UAE client for UAE workspace)
                                $this->countryTaxCode = $wsCountry;
                                $this->applyCountryTaxPreset($wsCountry);
                                if ($workspace->default_tax_rate !== null && (float)$workspace->default_tax_rate > 0) {
                                    $this->taxRate = (float) $workspace->default_tax_rate;
                                }
                            } else {
                                // Cross-border export for non-CA workspace
                                $this->countryTaxCode = 'EXPORT';
                                $this->taxName = 'Zero-Rated Export';
                                $this->taxRate = 0.00;
                                foreach ($this->items as $i => $item) {
                                    $this->items[$i]['tax_rate'] = 0.00;
                                }
                            }
                        }
                    }
                }
            }
            $this->recalculate();
        } elseif ($propertyName === 'walkinProvince') {
            $this->recalculate();
        } elseif ($propertyName === 'issueDate') {
            $this->updatedIssueDate($this->issueDate);
        } elseif ($propertyName === 'dueDate') {
            $this->updatedDueDate($this->dueDate);
        } elseif ($propertyName === 'taxRate') {
            foreach ($this->items as $i => $item) {
                $this->items[$i]['tax_rate'] = (float) $this->taxRate;
            }
        } elseif (is_string($propertyName) && str_starts_with($propertyName, 'items.') && str_ends_with($propertyName, '.service_id')) {
            $parts = explode('.', $propertyName);
            $idx = (int) $parts[1];
            $val = $this->items[$idx]['service_id'] ?? '';
            $this->selectService($idx, (string) $val);
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

    public function updatedItems($value = null, $key = null)
    {
        if ($key && is_string($key) && str_ends_with($key, '.service_id')) {
            $parts = explode('.', $key);
            $idx = (int) $parts[0];
            $this->selectService($idx, (string) $value);
        }

        $this->recalculate();
    }

    public function removeItem(int $index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        } else {
            // Reset the only remaining row to clean empty state
            $this->items = [[
                'service_id' => '',
                'description' => '',
                'category' => 'General',
                'quantity' => 1,
                'unit' => 'Project',
                'rate' => 0.00,
                'tax_rate' => $this->taxRate,
                'amount' => 0.00,
            ]];
        }
        $this->recalculate();
    }

    public function selectService($index, $serviceId = '')
    {
        $index = (int) $index;
        $serviceId = (string) $serviceId;

        if (!isset($this->items[$index])) {
            return;
        }

        if (empty($serviceId)) {
            $this->items[$index]['service_id'] = '';
            if (empty($this->items[$index]['unit'])) {
                $this->items[$index]['unit'] = 'Project';
            }
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

        $client = ($this->clientId && $this->clientId !== 'walkin') ? Client::find($this->clientId) : null;
        $workspace = Auth::user()?->workspace;

        if ($this->countryTaxCode === 'EXPORT' || ($workspace && $workspace->isCanadianWorkspace() && $client && strtoupper($client->country_code ?? 'CA') !== 'CA')) {
            $this->taxName = 'Zero-Rated Export';
            $this->taxRate = 0.00;
            $this->taxAmount = 0.00;
            $this->federalTaxName = 'Zero-Rated Export';
            $this->federalTaxRate = 0.00;
            $this->federalTaxAmount = 0.00;
            $this->provincialTaxName = null;
            $this->provincialTaxRate = 0.00;
            $this->provincialTaxAmount = 0.00;
            $this->isDualTax = false;
            $this->taxCalculationNote = 'CANADIAN EXPORT RULE: Zero-Rated International Supply (0.00%)';
            $this->subtotal = max(0, $subtotalCents) / 100;
            $this->totalAmount = max(0, $subtotalCents - $discountCents) / 100;
        } elseif ($this->countryTaxCode === 'CA' && $workspace && $workspace->isCanadianWorkspace()) {
            $overrideProv = $this->isWalkin ? ($this->walkinProvince ?: ($workspace->province ?? 'ON')) : null;
            $calcs = $this->taxService->calculateForClient(
                $workspace,
                $client,
                $subtotalCents,
                $discountCents,
                (float) $this->taxRate,
                $this->taxName,
                $overrideProv
            );

            $this->taxName = $calcs['tax_name'];
            $this->taxRate = (float) $calcs['tax_rate'];
            $this->taxAmount = $calcs['tax_cents'] / 100;

            $this->federalTaxName = $calcs['federal_tax_name'];
            $this->federalTaxRate = (float) $calcs['federal_tax_rate'];
            $this->federalTaxAmount = $calcs['federal_tax_cents'] / 100;

            $this->provincialTaxName = $calcs['provincial_tax_name'];
            $this->provincialTaxRate = (float) $calcs['provincial_tax_rate'];
            $this->provincialTaxAmount = $calcs['provincial_tax_cents'] / 100;

            $this->isDualTax = (bool) $calcs['is_dual_tax'];
            $this->taxCalculationNote = $calcs['note'];

            $this->subtotal = $calcs['subtotal_cents'] / 100;
            $this->totalAmount = $calcs['total_cents'] / 100;
        } else {
            $calcs = $this->taxService->calculateTotals(
                $subtotalCents,
                $discountCents,
                (float) $this->taxRate,
                $client
            );

            $this->subtotal = $calcs['subtotal_cents'] / 100;
            $this->taxAmount = $calcs['tax_cents'] / 100;
            $this->totalAmount = $calcs['total_cents'] / 100;
            $this->federalTaxName = $this->taxName;
            $this->federalTaxRate = (float) $this->taxRate;
            $this->federalTaxAmount = $this->taxAmount;
            $this->provincialTaxName = null;
            $this->provincialTaxRate = 0.00;
            $this->provincialTaxAmount = 0.00;
            $this->isDualTax = false;
            $this->taxCalculationNote = null;
        }
    }

    /**
     * Trigger interactive pre-dispatch review screen before issuing/emailing invoice (Item 3).
     */
    public function requestPublishReview()
    {
        $rules = [
            'issueDate' => 'required|date',
            'dueDate' => 'required|date|after_or_equal:issueDate',
            'currency' => 'required|string|size:3',
            'items.*.description' => 'nullable|string',
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

        if (empty($this->items)) {
            $this->addError('items', 'Please add at least one line item to the invoice.');
            return;
        }

        $this->calculateTotals();
        $this->showReviewModal = true;
    }

    public function closeReviewModal()
    {
        $this->showReviewModal = false;
    }

    public function confirmAndPublish()
    {
        $this->showReviewModal = false;
        $this->saveInvoice('issued');
    }

    public function saveInvoice(string $status = 'issued')
    {
        // Validation rules
        $rules = [
            'issueDate' => 'required|date',
            'dueDate' => 'required|date|after_or_equal:issueDate',
            'currency' => 'required|string|size:3',
            'items.*.description' => 'nullable|string',
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
            $walkEmail = !empty($this->walkinEmail) ? trim($this->walkinEmail) : null;
            $searchCriteria = [
                'workspace_id' => $workspace->id,
                'name' => $this->walkinName ?: 'Walk-in Customer',
            ];
            if (!empty($walkEmail)) {
                $searchCriteria['email'] = $walkEmail;
            }
            $effectiveWalkinProv = $workspace->isCanadianWorkspace() ? ($this->walkinProvince ?: ($workspace->province ?? 'ON')) : null;
            $client = Client::firstOrCreate(
                $searchCriteria,
                [
                    'email' => $walkEmail,
                    'phone' => $this->walkinPhone,
                    'address' => $this->walkinAddress,
                    'province' => $effectiveWalkinProv,
                    'country_code' => $workspace->isCanadianWorkspace() ? 'CA' : (($this->countryTaxCode && $this->countryTaxCode !== 'EXPORT') ? $this->countryTaxCode : ($workspace->country_code ?: 'US')),
                    'preferred_currency' => $this->currency ?: 'USD',
                ]
            );
            if ($workspace->isCanadianWorkspace() && $effectiveWalkinProv && $client->province !== $effectiveWalkinProv) {
                $client->update(['province' => $effectiveWalkinProv]);
            }
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
            'cra_bn_number' => $workspace->cra_bn_number,
            'qst_number' => $workspace->qst_number,
        ];

        $effectiveClientProv = $this->isWalkin
            ? ($this->walkinProvince ?: ($workspace->province ?? 'ON'))
            : ($client->province ?? $client->state ?? null);
        $effectiveClientCountry = $this->isWalkin
            ? ($workspace->isCanadianWorkspace() ? 'CA' : (($this->countryTaxCode && $this->countryTaxCode !== 'EXPORT') ? $this->countryTaxCode : ($workspace->country_code ?? 'US')))
            : ($client->country_code ?? 'US');

        $clientSnapshot = [
            'name' => $client->name,
            'company_name' => $client->company_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'tax_id' => $client->tax_id,
            'address' => $client->address,
            'city' => $client->city,
            'province' => $effectiveClientProv,
            'state' => $effectiveClientProv,
            'country_code' => $effectiveClientCountry,
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
                'language' => $this->language,
                'subtotal_cents' => $subtotalCents,
                'tax_name' => $this->taxName,
                'tax_rate' => $this->taxRate,
                'tax_cents' => $taxCents,
                'federal_tax_name' => $this->federalTaxName ?: $this->taxName,
                'federal_tax_rate' => $this->federalTaxRate ?: $this->taxRate,
                'federal_tax_cents' => (int) round($this->federalTaxAmount * 100),
                'provincial_tax_name' => $this->provincialTaxName,
                'provincial_tax_rate' => $this->provincialTaxRate,
                'provincial_tax_cents' => (int) round($this->provincialTaxAmount * 100),
                'is_dual_tax' => $this->isDualTax,
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
                'language' => $this->language,
                'subtotal_cents' => $subtotalCents,
                'tax_name' => $this->taxName,
                'tax_rate' => $this->taxRate,
                'tax_cents' => $taxCents,
                'federal_tax_name' => $this->federalTaxName ?: $this->taxName,
                'federal_tax_rate' => $this->federalTaxRate ?: $this->taxRate,
                'federal_tax_cents' => (int) round($this->federalTaxAmount * 100),
                'provincial_tax_name' => $this->provincialTaxName,
                'provincial_tax_rate' => $this->provincialTaxRate,
                'provincial_tax_cents' => (int) round($this->provincialTaxAmount * 100),
                'is_dual_tax' => $this->isDualTax,
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

        // Option 13: Direct Email Dispatch to registered client on publish (Never on drafts, never to dummy walkin emails)
        if ($status !== 'draft' && !$this->isScheduled && $this->sendEmailToClient && !empty($client->email) && !str_starts_with($client->email, 'walkin_')) {
            $currentUser = Auth::user();

            // 1. Phishing & Spam Relay Prevention: Require Verified Email
            if ($currentUser && !$currentUser->hasVerifiedEmail()) {
                session()->flash('warning', 'Email Verification Required: Invoice #' . $invoice->invoice_number . ' was created, but customer email dispatch to ' . $client->email . ' was blocked because your account email (' . $currentUser->email . ') is unverified.');
            } elseif (!$workspace->isTrial() && (!$currentUser || !$currentUser->isSuperAdmin()) && !$workspace->hasConfiguredSmtp()) {
                session()->flash('warning', "Invoice #{$invoice->invoice_number} created, but email dispatch paused: Subscribed agencies are required to configure their authenticated Outgoing SMTP server in Agency Settings before emailing clients.");
            } elseif ($workspace->hasReachedDailyEmailCap()) {
                $cap = $workspace->dailyEmailCap();
                session()->flash('warning', "Daily Send Cap Reached: Invoice #{$invoice->invoice_number} was created, but email dispatch was blocked because your workspace reached its daily limit of {$cap} outbound emails.");
            } else {
                try {
                    if (!empty($workspace->smtp_settings_encrypted)) {
                        $smtp = $workspace->smtp_settings_encrypted;
                        if (!empty($smtp['host']) && !empty($smtp['username'])) {
                            config([
                                'mail.mailers.smtp.host' => $smtp['host'],
                                'mail.mailers.smtp.port' => $smtp['port'] ?? 465,
                                'mail.mailers.smtp.encryption' => $smtp['encryption'] ?? 'ssl',
                                'mail.mailers.smtp.username' => $smtp['username'],
                                'mail.mailers.smtp.password' => $smtp['password'] ?? '',
                                'mail.from.address' => $smtp['from_address'] ?? $smtp['username'],
                                'mail.from.name' => $smtp['from_name'] ?? ($workspace->name ?: 'Invoicy by RedMaple'),
                            ]);
                        }
                    }

                    Mail::to($client->email)->send(new InvoiceMailable($invoice));
                    $invoice->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    $workspace->incrementDailyEmailCount();

                    \App\Services\AuditService::log('invoice.dispatched_email', $invoice, null, [
                        'recipient' => $client->email,
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to email invoice {$invoice->id}: " . $e->getMessage());
                }
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
