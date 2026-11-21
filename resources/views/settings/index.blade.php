@extends('layouts.app', [
    'title' => (auth()->user() && auth()->user()->isSuperAdmin()) ? 'System Settings - Invoicy' : 'Agency Settings - Invoicy',
    'header' => (auth()->user() && auth()->user()->isSuperAdmin()) ? __('System Settings') : __('Agency Settings')
])

@section('content')
<div class="w-full space-y-8">

    <div class="flex items-center justify-between">
        <div>
            @if(auth()->user() && auth()->user()->isSuperAdmin())
                <h2 class="text-xl font-bold text-slate-900">System & Platform Configuration</h2>
                <p class="text-xs text-slate-500 mt-0.5">Configure platform defaults, payment gateway integrations, outgoing mail servers, and administration credentials</p>
            @else
                <h2 class="text-xl font-bold text-slate-900">Workspace & Invoicing Configuration</h2>
                <p class="text-xs text-slate-500 mt-0.5">Manage your agency profile, wire transfer details, brand styling, SMTP email, and security</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user() && auth()->user()->isSuperAdmin())
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                    <i class="ph ph-shield-check text-amber-600 text-sm"></i>
                    Platform Super Administrator
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                    <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                    Tenant: {{ $workspace->slug }}
                </span>
            @endif
        </div>
    </div>

    <!-- Settings Sub-Navigation Shortcuts Menu (Item 6) -->
    <div class="bg-white/95 backdrop-blur-md p-2 rounded-2xl border border-slate-200 shadow-xs sticky top-3 z-30">
        <nav class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 text-xs font-bold">
            <a href="#profile" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-user text-sm text-slate-400"></i>
                <span>Profile</span>
            </a>
            <span class="text-slate-300 shrink-0">&bull;</span>
            <a href="#agency" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-buildings text-sm text-slate-400"></i>
                <span>Agency</span>
            </a>
            <span class="text-slate-300 shrink-0">&bull;</span>
            <a href="#regional" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-globe text-sm text-slate-400"></i>
                <span>Regional</span>
            </a>
            <span class="text-slate-300 shrink-0">&bull;</span>
            <a href="#wire" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-bank text-sm text-slate-400"></i>
                <span>Wire Details</span>
            </a>
            <span class="text-slate-300 shrink-0">&bull;</span>
            <a href="#brand" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-palette text-sm text-slate-400"></i>
                <span>Brand & Logo</span>
            </a>
            <span class="text-slate-300 shrink-0">&bull;</span>
            <a href="#payments" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-credit-card text-sm text-slate-400"></i>
                <span>Payments</span>
            </a>
            <span class="text-slate-300 shrink-0">&bull;</span>
            <a href="#smtp" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-envelope-simple text-sm text-slate-400"></i>
                <span>SMTP</span>
            </a>
            <span class="text-slate-300 shrink-0">&bull;</span>
            <a href="#security" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-shield-check text-sm text-slate-400"></i>
                <span>Security</span>
            </a>
            <span class="text-slate-300 shrink-0">&bull;</span>
            <a href="#team" class="shrink-0 px-3 py-1.5 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                <i class="ph ph-users-three text-sm text-slate-400"></i>
                <span>Team Seats</span>
            </a>
        </nav>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-xs">
            <div class="font-bold flex items-center gap-2">
                <i class="ph ph-warning-circle text-base text-rose-600 shrink-0"></i>
                <span>There were errors saving your settings:</span>
            </div>
            <ul class="list-disc list-inside pl-4 text-[11px] font-normal space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('password_success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-shield-check text-base text-emerald-600"></i>
            {{ session('password_success') }}
        </div>
    @endif
    @if (session('smtp_success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-envelope-simple text-base text-emerald-600"></i>
            {{ session('smtp_success') }}
        </div>
    @endif
    @if (session('smtp_error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-warning-circle text-base text-rose-600"></i>
            {{ session('smtp_error') }}
        </div>
    @endif
    @if (session('profile_success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-user-check text-base text-emerald-600"></i>
            {{ session('profile_success') }}
        </div>
    @endif
    @if (session('team_success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-users-three text-base text-emerald-600"></i>
            {{ session('team_success') }}
        </div>
    @endif
    @if (session('team_error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-warning-circle text-base text-rose-600"></i>
            {{ session('team_error') }}
        </div>
    @endif

    <!-- Card 0: Owner / Super Admin Contact & Personal Profile -->
    <div id="profile" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="ph ph-user-circle"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-base">
                    {{ (auth()->user() && auth()->user()->is_super_admin) ? 'Super Admin Profile & Account Contact' : 'Owner Profile & Account Contact' }}
                </h3>
                <p class="text-xs text-slate-500">
                    {{ (auth()->user() && auth()->user()->is_super_admin) ? 'Update your platform super administrator profile name and login email' : 'Update your personal contact name and email displayed in the bottom sidebar and account communications' }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.profile') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ (auth()->user() && auth()->user()->is_super_admin) ? 'Super Admin Full Name *' : 'Owner Full Name *' }}
                    </label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                           placeholder="e.g. Alex Rivera"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 font-semibold outline-none bg-white">
                    @error('name') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ (auth()->user() && auth()->user()->is_super_admin) ? 'Super Admin Account Email (Login & Contact) *' : 'Owner Account Email (Login & Contact) *' }}
                    </label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                           placeholder="owner@domain.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 font-semibold outline-none bg-white">
                    @error('email') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                    <i class="ph ph-floppy-disk text-base"></i>
                    {{ (auth()->user() && auth()->user()->is_super_admin) ? 'Update Super Admin Profile' : 'Update Owner Profile' }}
                </button>
            </div>
        </form>
    </div>

    <script>
    (function() {
        function initSettingsPresets() {
            if (typeof Alpine !== 'undefined' && Alpine.data) {
                Alpine.data('settingsRegionalManager', () => ({
                    selectedCountry: '{{ strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) }}',
                    originalCountry: '{{ strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) }}',
                    selectedCurrency: '{{ strtoupper(old('currency', $workspace->currency ?? 'USD')) }}',
                    taxId: @json(old('tax_id', $workspace->tax_id ?? '')),
                    bankInstructions: @json(old('bank_transfer_instructions', $workspace->bank_transfer_instructions ?? '')),
                    craBnNumber: @json(old('cra_bn_number', $workspace->cra_bn_number ?? '')),
                    qstNumber: @json(old('qst_number', $workspace->qst_number ?? '')),
                    smallSupplierExempt: {{ old('small_supplier_exempt', $workspace->small_supplier_exempt ?? false) ? 'true' : 'false' }},
                    collectQst: {{ old('collect_qst', $workspace->collect_qst ?? true) ? 'true' : 'false' }},
                    collectPst: {{ old('collect_pst', $workspace->collect_pst ?? true) ? 'true' : 'false' }},
                    selectedProvince: '{{ old('province', $workspace->province ?? 'ON') }}',
                    defaultTaxRate: {{ old('default_tax_rate', $workspace->default_tax_rate ?? 0.00) }},
                    showPresetPrompt: false,
                    promptCountry: '',

                    canadianProvinces: {
                        ON: { name: 'Ontario', rate: 13.000, taxName: 'HST (13.00%)', note: '13% Harmonized Sales Tax (HST)' },
                        QC: { name: 'Quebec', rate: 14.975, taxName: 'GST (5%) + QST (9.975%)', note: '5% Federal GST + 9.975% Revenu Québec QST (Non-compounded)' },
                        BC: { name: 'British Columbia', rate: 12.000, taxName: 'GST (5%) + PST (7%)', note: '5% Federal GST + 7% BC PST' },
                        AB: { name: 'Alberta', rate: 5.000, taxName: 'GST (5.00%)', note: '5% Federal GST (No Provincial Sales Tax)' },
                        MB: { name: 'Manitoba', rate: 12.000, taxName: 'GST (5%) + PST (7%)', note: '5% Federal GST + 7% Manitoba PST' },
                        SK: { name: 'Saskatchewan', rate: 11.000, taxName: 'GST (5%) + PST (6%)', note: '5% Federal GST + 6% Saskatchewan PST' },
                        NS: { name: 'Nova Scotia', rate: 14.000, taxName: 'HST (14.00%)', note: '14% Harmonized Sales Tax (HST)' },
                        NB: { name: 'New Brunswick', rate: 15.000, taxName: 'HST (15.00%)', note: '15% Harmonized Sales Tax (HST)' },
                        NL: { name: 'Newfoundland and Labrador', rate: 15.000, taxName: 'HST (15.00%)', note: '15% Harmonized Sales Tax (HST)' },
                        PE: { name: 'Prince Edward Island', rate: 15.000, taxName: 'HST (15.00%)', note: '15% Harmonized Sales Tax (HST)' },
                        NT: { name: 'Northwest Territories', rate: 5.000, taxName: 'GST (5.00%)', note: '5% Federal GST' },
                        NU: { name: 'Nunavut', rate: 5.000, taxName: 'GST (5.00%)', note: '5% Federal GST' },
                        YT: { name: 'Yukon', rate: 5.000, taxName: 'GST (5.00%)', note: '5% Federal GST' }
                    },

                    get currentProvinceInfo() {
                        const prov = this.canadianProvinces[this.selectedProvince] || this.canadianProvinces.ON;
                        if (this.smallSupplierExempt) {
                            return {
                                name: prov.name,
                                rate: 0.000,
                                taxName: 'Exempt (0.00%)',
                                note: 'Small-Supplier Exemption Active (< $30,000 CAD) — 0.00% tax collected'
                            };
                        }
                        let rate = prov.rate;
                        let taxName = prov.taxName;
                        let note = prov.note;
                        if (this.selectedProvince === 'QC' && !this.collectQst) {
                            rate = 5.000;
                            taxName = 'GST (5.00%)';
                            note = '5% Federal GST Only (Revenu Québec QST Collection Disabled)';
                        } else if (['BC', 'MB'].includes(this.selectedProvince) && !this.collectPst) {
                            rate = 5.000;
                            taxName = 'GST (5.00%)';
                            note = '5% Federal GST Only (Provincial PST Collection Disabled)';
                        } else if (this.selectedProvince === 'SK' && !this.collectPst) {
                            rate = 5.000;
                            taxName = 'GST (5.00%)';
                            note = '5% Federal GST Only (Saskatchewan PST Collection Disabled)';
                        }
                        return { name: prov.name, rate, taxName, note };
                    },

                    onProvinceChange() {
                        this.defaultTaxRate = this.currentProvinceInfo.rate;
                    },

                    init() {
                        if (this.selectedCountry === 'CA') {
                            this.onProvinceChange();
                        }
                    },

                    presets: {
                        CA: {
                            name: 'Canada',
                            currency: 'CAD',
                            taxRate: 0.00,
                            taxLabel: 'Dynamic Place-of-Supply (5% - 15% GST/HST/QST/PST)',
                            taxIdLabel: 'CRA Business Number (BN / GST/HST)',
                            taxIdPlaceholder: '123456789 RT0001',
                            engineNote: 'Place of Supply Engine: 13% ON, 14% NS, 15% NB/NL/PE, 5% AB/BC/SK/MB/Territories + Provincial Tax',
                            bankInstructions: 'Interac e-Transfer to billing@yourcompany.ca (Auto-deposit enabled). Please include Invoice # in the message.'
                        },
                        AE: {
                            name: 'United Arab Emirates',
                            currency: 'AED',
                            taxRate: 5.00,
                            taxLabel: 'VAT (5.00%)',
                            taxIdLabel: 'Tax Registration Number (TRN)',
                            taxIdPlaceholder: '100XXXXXXXXXXXX (15 digits)',
                            engineNote: 'Federal Tax Authority (FTA) Standard 5.00% VAT',
                            bankInstructions: "Bank Name: Emirates NBD / ADCB\nAccount Name: Your Company LLC\nIBAN: AE000000000000000000000\nSWIFT/BIC: EBILAEAD\nReference: Invoice #"
                        },
                        SA: {
                            name: 'Saudi Arabia',
                            currency: 'SAR',
                            taxRate: 15.00,
                            taxLabel: 'VAT (15.00%)',
                            taxIdLabel: 'ZATCA VAT Registration Number',
                            taxIdPlaceholder: '3XXXXXXXXXXXXX3 (15 digits starting & ending with 3)',
                            engineNote: 'ZATCA Phase 1 & 2 Electronic Invoicing Standard',
                            bankInstructions: "Bank Name: Al Rajhi / SNB\nAccount Name: Your Company Co.\nIBAN: SA0000000000000000000000\nSWIFT/BIC: RJHISARI\nReference: Invoice #"
                        },
                        TR: {
                            name: 'Turkey',
                            currency: 'TRY',
                            taxRate: 20.00,
                            taxLabel: 'KDV (20.00%)',
                            taxIdLabel: 'Vergi Kimlik Numarası (VKN / TCKN)',
                            taxIdPlaceholder: '1234567890 (10 digits)',
                            engineNote: 'GIB Standart Katma Değer Vergisi (%20 KDV)',
                            bankInstructions: "Banka Adı: Garanti BBVA / İş Bankası\nHesap Sahibi: Şirket Ünvanı Ltd. Şti.\nIBAN: TR00 0000 0000 0000 0000 0000 00\nAçıklama / Referans: Fatura #"
                        },
                        US: {
                            name: 'United States',
                            currency: 'USD',
                            taxRate: 0.00,
                            taxLabel: 'Sales Tax (0.00%)',
                            taxIdLabel: 'EIN / Tax ID',
                            taxIdPlaceholder: 'XX-XXXXXXX',
                            engineNote: 'US State & Local Sales Tax Nexus Rules (Destination-based)',
                            bankInstructions: "ACH / Domestic Wire Transfer\nBank Name: ...\nRouting (ABA): ...\nAccount Number: ...\nReference: Invoice #"
                        },
                        GB: {
                            name: 'United Kingdom',
                            currency: 'GBP',
                            taxRate: 20.00,
                            taxLabel: 'VAT (20.00%)',
                            taxIdLabel: 'UK VAT Registration Number',
                            taxIdPlaceholder: 'GB 123 4567 89',
                            engineNote: 'HMRC Making Tax Digital Standard 20.00% VAT',
                            bankInstructions: "Faster Payments / BACS Transfer\nAccount Name: ...\nSort Code: XX-XX-XX\nAccount Number: XXXXXXXX\nReference: Invoice #"
                        },
                        DE: {
                            name: 'Germany',
                            currency: 'EUR',
                            taxRate: 19.00,
                            taxLabel: 'MwSt (19.00%)',
                            taxIdLabel: 'Umsatzsteuer-Identifikationsnummer (USt-IdNr.)',
                            taxIdPlaceholder: 'DE123456789',
                            engineNote: 'German Standard 19.00% Umsatzsteuer (MwSt)',
                            bankInstructions: "SEPA Bank Transfer\nBank Name: Deutsche Bank\nIBAN: DE00 0000 0000 0000 0000 00\nBIC: DEUTDEDDXXX\nReference: Invoice #"
                        },
                        FR: {
                            name: 'France',
                            currency: 'EUR',
                            taxRate: 20.00,
                            taxLabel: 'TVA (20.00%)',
                            taxIdLabel: 'Numéro de TVA Intracommunautaire',
                            taxIdPlaceholder: 'FR 12 345678901',
                            engineNote: 'French Standard 20.00% TVA',
                            bankInstructions: "Virement SEPA\nBanque: BNP Paribas\nIBAN: FR76 0000 0000 0000 0000 0000 000\nBIC: BNPAFR2X\nRéférence: Facture #"
                        },
                        PK: {
                            name: 'Pakistan',
                            currency: 'PKR',
                            taxRate: 16.00,
                            taxLabel: 'Sales Tax / SST (16.00%)',
                            taxIdLabel: 'National Tax Number (NTN / STRN)',
                            taxIdPlaceholder: '1234567-8',
                            engineNote: 'FBR / Provincial Revenue Authority Standard Sales Tax',
                            bankInstructions: "Bank Account Title: Company Ltd.\nBank: Meezan Bank / HBL\nIBAN: PK00MEZN0000000000000000\nReference: Invoice #"
                        },
                        IN: {
                            name: 'India',
                            currency: 'INR',
                            taxRate: 18.00,
                            taxLabel: 'GST (18.00%)',
                            taxIdLabel: 'GSTIN (Goods and Services Tax ID)',
                            taxIdPlaceholder: '22AAAAA0000A1Z5',
                            engineNote: 'GST Council Standard 18.00% GST (CGST + SGST / IGST)',
                            bankInstructions: "Bank Name: HDFC Bank / ICICI\nAccount Name: Company Private Ltd.\nAccount Number: 000000000000\nIFSC Code: HDFC0000000\nReference: Invoice #"
                        },
                        AU: {
                            name: 'Australia',
                            currency: 'AUD',
                            taxRate: 10.00,
                            taxLabel: 'GST (10.00%)',
                            taxIdLabel: 'Australian Business Number (ABN)',
                            taxIdPlaceholder: '12 345 678 901',
                            engineNote: 'ATO Standard 10.00% Goods and Services Tax (GST)',
                            bankInstructions: "BSB: 000-000\nAccount Number: 0000 0000\nAccount Name: Company Pty Ltd\nBank: Commonwealth Bank / ANZ\nReference: Invoice #"
                        },
                        GLOBAL: {
                            name: 'Global / International',
                            currency: 'USD',
                            taxRate: 0.00,
                            taxLabel: 'Tax / VAT',
                            taxIdLabel: 'Tax ID / VAT Number',
                            taxIdPlaceholder: 'US-987654321 / GB123456',
                            engineNote: 'GAAP / IFRS International Pre-Tax Discount Standard',
                            bankInstructions: "International Wire Transfer / SWIFT\nBank Name: ...\nAccount / IBAN: ...\nSWIFT / BIC: ...\nReference: Invoice #"
                        }
                    },

                    get currentPreset() {
                        return this.presets[this.selectedCountry] || this.presets.GLOBAL;
                    },

                    onCountryChange() {
                        const countryCurrencyMap = {
                            CA: 'CAD',
                            AE: 'AED',
                            SA: 'SAR',
                            TR: 'TRY',
                            US: 'USD',
                            GB: 'GBP',
                            DE: 'EUR',
                            FR: 'EUR',
                            IT: 'EUR',
                            ES: 'EUR',
                            NL: 'EUR',
                            BE: 'EUR',
                            AT: 'EUR',
                            IE: 'EUR',
                            PT: 'EUR',
                            FI: 'EUR',
                            GR: 'EUR',
                            IN: 'INR',
                            PK: 'PKR',
                            AU: 'AUD'
                        };
                        if (countryCurrencyMap[this.selectedCountry]) {
                            this.selectedCurrency = countryCurrencyMap[this.selectedCountry];
                        }
                        if (this.selectedCountry === 'CA') {
                            this.selectedProvince = this.selectedProvince || 'ON';
                            this.onProvinceChange();
                        } else {
                            const p = this.presets[this.selectedCountry] || this.presets.GLOBAL;
                            this.defaultTaxRate = p.taxRate ?? 0.00;
                        }
                        if (this.selectedCountry !== this.originalCountry) {
                            this.promptCountry = (this.presets[this.selectedCountry] || { name: this.selectedCountry }).name;
                            this.showPresetPrompt = true;
                        } else {
                            this.showPresetPrompt = false;
                        }
                    },

                    applyRegionalDefaults() {
                        const p = this.currentPreset;
                        this.selectedCurrency = p.currency;
                        this.bankInstructions = p.bankInstructions;
                        if (this.selectedCountry === 'CA') {
                            this.selectedProvince = 'ON';
                            this.onProvinceChange();
                        } else {
                            this.defaultTaxRate = p.taxRate ?? 0.00;
                        }
                        this.originalCountry = this.selectedCountry;
                        this.showPresetPrompt = false;
                    },

                    dismissPresetPrompt() {
                        this.showPresetPrompt = false;
                    }
                }));
            } else {
                document.addEventListener('alpine:init', initSettingsPresets, { once: true });
            }
        }
        initSettingsPresets();
    })();
    </script>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" 
          x-data="settingsRegionalManager()"
          class="space-y-8">
        @csrf

        <!-- Card 1: Agency Business Profile -->
        <div id="agency" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-buildings"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Agency Profile & Contact Information</h3>
                    <p class="text-xs text-slate-500">Information displayed on generated invoices, customer portal, and PDF headers</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Agency / Company Name *</label>
                    <input type="text" name="name" value="{{ old('name', $workspace->name) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                    @error('name') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5"
                           x-text="currentPreset.taxIdLabel || 'VAT / Tax Identification Number'">VAT / Tax Identification Number</label>
                    <input type="text" name="tax_id" x-model="taxId"
                           :placeholder="currentPreset.taxIdPlaceholder || 'US-987654321 / GB123456'"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                    <p class="text-[11px] text-slate-500 mt-1" x-text="currentPreset.engineNote"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Billing Email</label>
                    <input type="email" name="email" value="{{ old('email', $workspace->email) }}" placeholder="billing@agency.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $workspace->phone) }}" placeholder="+1 (555) 019-2834"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Website URL</label>
                    <input type="url" name="website" value="{{ old('website', $workspace->website) }}" placeholder="https://agency.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Physical Business Address</label>
                <textarea name="address" rows="2" placeholder="Suite 100, 742 Evergreen Terrace..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">{{ old('address', $workspace->address) }}</textarea>
            </div>
        </div>

        <!-- Card 2: Regional Defaults & Multi-Language -->
        <div id="regional" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-globe"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Regional Defaults & Invoice Language</h3>
                    <p class="text-xs text-slate-500">Default currency, jurisdiction tax defaults, and multilingual presets</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Default Language</label>
                    <select name="default_language" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white font-medium">
                        <option value="en" {{ old('default_language', old('language', $workspace->language)) === 'en' ? 'selected' : '' }}>🇺🇸 English</option>
                        <option value="ur" {{ old('default_language', old('language', $workspace->language)) === 'ur' ? 'selected' : '' }}>🇵🇰 Urdu (اردو)</option>
                        <option value="hi" {{ old('default_language', old('language', $workspace->language)) === 'hi' ? 'selected' : '' }}>🇮🇳 Hindi (हिन्दी)</option>
                        <option value="tr" {{ old('default_language', old('language', $workspace->language)) === 'tr' ? 'selected' : '' }}>🇹🇷 Turkish (Türkçe)</option>
                        <option value="ar" {{ old('default_language', old('language', $workspace->language)) === 'ar' ? 'selected' : '' }}>🇦🇪 Arabic (العربية)</option>
                        <option value="es" {{ old('default_language', old('language', $workspace->language)) === 'es' ? 'selected' : '' }}>🇪🇸 Spanish (Español)</option>
                        <option value="fr" {{ old('default_language', old('language', $workspace->language)) === 'fr' ? 'selected' : '' }}>🇫🇷 French (Français)</option>
                        <option value="de" {{ old('default_language', old('language', $workspace->language)) === 'de' ? 'selected' : '' }}>🇩🇪 German (Deutsch)</option>
                        <option value="zh" {{ old('default_language', old('language', $workspace->language)) === 'zh' ? 'selected' : '' }}>🇨🇳 Chinese (中文)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Primary Currency</label>
                    <select name="currency" x-model="selectedCurrency" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white font-semibold">
                        <option value="USD" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'USD')>USD ($)</option>
                        <option value="CAD" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'CAD')>CAD (C$)</option>
                        <option value="AED" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'AED')>AED (د.إ)</option>
                        <option value="SAR" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'SAR')>SAR (﷼)</option>
                        <option value="TRY" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'TRY')>TRY (₺)</option>
                        <option value="GBP" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'GBP')>GBP (£)</option>
                        <option value="EUR" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'EUR')>EUR (€)</option>
                        <option value="AUD" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'AUD')>AUD (A$)</option>
                        <option value="INR" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'INR')>INR (₹)</option>
                        <option value="PKR" @selected(strtoupper(old('currency', $workspace->currency ?? 'USD')) === 'PKR')>PKR (Rs)</option>
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Tax Jurisdiction Country</label>
                        <span class="text-[10px] font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full" x-text="selectedCountry"></span>
                    </div>
                    <select name="country_code" x-model="selectedCountry" @change="onCountryChange()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white font-medium">
                        <option value="US" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'US')>🇺🇸 United States</option>
                        <option value="CA" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'CA')>🇨🇦 Canada</option>
                        <option value="AE" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'AE')>🇦🇪 United Arab Emirates (UAE)</option>
                        <option value="SA" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'SA')>🇸🇦 Saudi Arabia (KSA)</option>
                        <option value="TR" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'TR')>🇹🇷 Turkey (Türkiye)</option>
                        <option value="GB" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'GB')>🇬🇧 United Kingdom</option>
                        <option value="PK" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'PK')>🇵🇰 Pakistan</option>
                        <option value="IN" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'IN')>🇮🇳 India</option>
                        <option value="AU" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'AU')>🇦🇺 Australia</option>
                        <option value="DE" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'DE')>🇩🇪 Germany</option>
                        <option value="FR" @selected(strtoupper(old('country_code', old('country', $workspace->country_code ?? 'US'))) === 'FR')>🇫🇷 France</option>
                    </select>
                </div>
            </div>

            <!-- Canadian Province / Territory Selection & Dynamic Province Tax Rate -->
            <div x-show="selectedCountry === 'CA'" x-cloak x-transition
                 class="pt-5 border-t border-slate-100">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Canadian Province / Territory (Place of Supply)
                            </label>
                            <span class="text-[10px] font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full font-mono" x-text="selectedProvince"></span>
                        </div>
                        <select name="province" x-model="selectedProvince" @change="onProvinceChange()"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white font-semibold">
                            <option value="AB">🇨🇦 Alberta (AB - 5% GST)</option>
                            <option value="BC">🇨🇦 British Columbia (BC - 5% GST + 7% PST)</option>
                            <option value="MB">🇨🇦 Manitoba (MB - 5% GST + 7% PST)</option>
                            <option value="NB">🇨🇦 New Brunswick (NB - 15% HST)</option>
                            <option value="NL">🇨🇦 Newfoundland and Labrador (NL - 15% HST)</option>
                            <option value="NS">🇨🇦 Nova Scotia (NS - 14% HST)</option>
                            <option value="NT">🇨🇦 Northwest Territories (NT - 5% GST)</option>
                            <option value="NU">🇨🇦 Nunavut (NU - 5% GST)</option>
                            <option value="ON">🇨🇦 Ontario (ON - 13% HST)</option>
                            <option value="PE">🇨🇦 Prince Edward Island (PE - 15% HST)</option>
                            <option value="QC">🇨🇦 Quebec (QC - 5% GST + 9.975% QST)</option>
                            <option value="SK">🇨🇦 Saskatchewan (SK - 5% GST + 6% PST)</option>
                            <option value="YT">🇨🇦 Yukon (YT - 5% GST)</option>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Sets the default provincial tax jurisdiction for your agency and local retail customers.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Selected Province Tax Rate
                            </label>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full font-mono" x-text="currentProvinceInfo.taxName"></span>
                        </div>
                        <div class="relative">
                            <input type="number" step="0.001" name="default_tax_rate" x-model="defaultTaxRate" readonly
                                   :disabled="selectedCountry !== 'CA'"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono font-bold text-slate-900 outline-none">
                            <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400">%</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed" x-text="currentProvinceInfo.note"></p>
                    </div>
                </div>
            </div>

            <!-- Non-Canadian Standard Default Tax Rate -->
            <div x-show="selectedCountry !== 'CA'" x-cloak x-transition
                 class="pt-5 border-t border-slate-100">
                <div class="max-w-md">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Default Tax Rate (%)
                        </label>
                        <span class="text-[10px] font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full font-mono" x-text="currentPreset.taxLabel || 'VAT'"></span>
                    </div>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="default_tax_rate" x-model="defaultTaxRate"
                               :disabled="selectedCountry === 'CA'"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold text-slate-900 focus:ring-2 focus:ring-red-500 outline-none bg-white">
                        <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400">%</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 text-slate-500">
                        Default tax rate applied to domestic sales and standard non-export invoices.
                    </p>
                </div>
            </div>

            <!-- Dynamic Canadian Place-of-Supply Informational Badge -->
            <div x-show="selectedCountry === 'CA'" x-cloak
                 class="mt-4 p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 flex items-start gap-3">
                <i class="ph ph-info text-blue-600 text-lg shrink-0 mt-0.5"></i>
                <div>
                    <div class="font-bold text-xs text-blue-950 flex items-center gap-2">
                        <span>Dynamic Canadian Place-of-Supply Enabled</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase">Automated</span>
                    </div>
                    <p class="text-[11px] text-blue-800 mt-0.5 leading-relaxed">
                        Tax rates (5% to 15% GST/HST/QST/PST) are automatically calculated from each client's province when creating an invoice. Non-Canadian clients are automatically zero-rated (0.00%).
                    </p>
                </div>
            </div>

            <!-- Non-Destructive Regional Preset Confirmation Banner -->
            <div x-show="showPresetPrompt" x-cloak x-transition
                 class="mt-4 p-4 rounded-2xl bg-gradient-to-r from-red-50 via-rose-50 to-amber-50 border-2 border-red-300 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                        <i class="ph ph-magic-wand"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                            <span>Apply <span x-text="promptCountry" class="text-red-700 font-extrabold"></span> Regional Defaults?</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200 uppercase tracking-wider" x-text="selectedCountry"></span>
                        </h4>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                            Adapts Primary Currency to <strong class="font-mono text-slate-900" x-text="currentPreset.currency"></strong>, sets tax labels to <strong x-text="currentPreset.taxLabel"></strong>, and loads pre-formatted remittance transfer instructions. Your other saved settings will be preserved.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
                    <button type="button" @click="applyRegionalDefaults()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-check-circle text-sm"></i>
                        <span>Apply Defaults</span>
                    </button>
                    <button type="button" @click="dismissPresetPrompt()"
                            class="px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl border border-slate-200 transition cursor-pointer">
                        Keep Current
                    </button>
                </div>
            </div>
        </div>

        <!-- Dedicated Canadian Tax & Compliance Card (CRA & Revenu Québec) -->
        <div x-show="selectedCountry === 'CA'" x-cloak x-transition
             class="bg-white rounded-2xl border-2 border-red-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shrink-0">
                        <svg class="w-6 h-6 text-red-600" viewBox="0 0 900 900" fill="currentColor">
                            <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                            <span>Canadian Tax Compliance & Place-of-Supply Engine</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-300">
                                CRA & Revenu Québec
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500">Configure your 15-character CRA Business Number, QST program ID, and provincial dual-tax collection</p>
                    </div>
                </div>
            </div>

            <!-- Small Supplier Exemption Banner & Toggle -->
            <div class="p-4 rounded-xl border transition"
                 :class="smallSupplierExempt ? 'bg-amber-50 border-amber-200 text-amber-950' : 'bg-slate-50 border-slate-200 text-slate-800'">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="font-bold text-xs flex items-center gap-1.5">
                            <i class="ph ph-shield-check text-sm text-amber-600"></i>
                            <span>CRA Small-Supplier Exemption Rule (&lt; $30,000 CAD)</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Under Excise Tax Act rules, Canadian businesses with gross worldwide revenue of $30,000 or less over 4 consecutive calendar quarters are not legally required to register for or collect GST/HST.
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="small_supplier_exempt" value="1"
                               x-model="smallSupplierExempt"
                               @change="onProvinceChange()"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                    </label>
                </div>
                <template x-if="smallSupplierExempt">
                    <div class="mt-3 pt-3 border-t border-amber-200/80 text-[11px] text-amber-900 font-semibold flex items-center gap-1.5">
                        <i class="ph ph-info text-base text-amber-700"></i>
                        <span>Small-Supplier Exemption Active: GST/HST collection is suppressed (0%) on issued invoices.</span>
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        CRA Business Number (BN / GST/HST)
                    </label>
                    <input type="text" name="cra_bn_number" x-model="craBnNumber"
                           placeholder="123456789 RT0001"
                           maxlength="30"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-red-500 outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">
                        Format: 9-digit Business Number + 2-letter program code (RT) + 4-digit account reference. Printed on Canadian client invoices for CRA input tax credits.
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Revenu Québec QST Number (TQ)
                    </label>
                    <input type="text" name="qst_number" x-model="qstNumber"
                           placeholder="1234567890 TQ0001"
                           maxlength="30"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-red-500 outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">
                        Format: 10-digit identification number + TQ + 4-digit program reference. Mandatory if registering for Quebec Sales Tax (QST).
                    </p>
                </div>
            </div>

            <!-- Dual-Tax Provincial Collection Toggles -->
            <div class="pt-3 border-t border-slate-100 space-y-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Provincial Dual-Tax Engine Rules</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="collect_qst" value="1" x-model="collectQst" @change="onProvinceChange()"
                               class="mt-0.5 w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Collect Quebec Sales Tax (9.975% QST)</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Calculated as a separate non-compounded line on invoices issued to Quebec clients.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="collect_pst" value="1" x-model="collectPst" @change="onProvinceChange()"
                               class="mt-0.5 w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Collect Provincial Sales Tax (PST)</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Applies separate line rates in British Columbia (7%), Saskatchewan (6%), and Manitoba (7%).</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Card 3: Bank Details for Invoices -->
        @php
            $bank = is_array($workspace->bank_details) ? $workspace->bank_details : (json_decode($workspace->bank_details, true) ?? []);
        @endphp
        <div id="wire" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-bank"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Bank Wire & Remittance Instructions</h3>
                    <p class="text-xs text-slate-500">Printed directly on customer PDF invoices and payment portal instructions</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $bank['bank_name'] ?? '') }}" placeholder="Silicon Valley Commercial Bank"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Account / IBAN Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $bank['account_number'] ?? '') }}" placeholder="US89 3704 0044 0532 0130 00"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Routing / Sort Code</label>
                    <input type="text" name="routing_number" value="{{ old('routing_number', $bank['routing_number'] ?? '') }}" placeholder="121000358"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">SWIFT / BIC Code</label>
                    <input type="text" name="swift" value="{{ old('swift', $bank['swift'] ?? '') }}" placeholder="SVCBUS33"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="enable_bank_transfer" value="1" {{ old('enable_bank_transfer', $workspace->enable_bank_transfer ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                    <span class="text-sm font-semibold text-slate-800">Enable Bank Transfer & Interac e-Transfer for Client Invoices</span>
                </label>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Bank Transfer & Remittance Instructions
                        </label>
                        <button type="button" 
                                @click="bankInstructions = currentPreset.bankInstructions"
                                class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition cursor-pointer">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                            <span>Load <span x-text="currentPreset.name"></span> Template</span>
                        </button>
                    </div>
                    <textarea name="bank_transfer_instructions" rows="4"
                              x-model="bankInstructions"
                              :placeholder="currentPreset.bankInstructions"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('bank_transfer_instructions', $workspace->bank_transfer_instructions ?? '') }}</textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Rendered on customer payment portals and invoice PDFs with the required Invoice Number reference.</p>
                </div>
            </div>
        </div>

        <!-- Card 4: Brand Identity, Custom Logo & Colors -->
        <div id="brand" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-palette"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Brand Customization, Logo & Colors</h3>
                    <p class="text-xs text-slate-500">Update agency logo and accent colors across your invoices and client portal</p>
                </div>
            </div>

            <!-- Logo Upload & Customization -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Agency Brand Logo</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Displays in app header, customer payment portal, and PDF invoices</p>
                    </div>
                    @if($workspace->logo_url)
                        <label class="flex items-center gap-1.5 text-xs text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                            Reset to Default RedMaple Logo
                        </label>
                    @endif
                </div>

                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-xl border border-slate-300 bg-white p-2 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                        @if($workspace->logo_url)
                            <img src="{{ $workspace->logo_url }}" alt="Agency Logo" class="w-full h-full object-contain">
                        @else
                            <svg class="w-full h-full text-red-600" viewBox="0 0 900 900" fill="currentColor">
                                <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 space-y-2">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Upload New Logo (PNG, JPG, SVG, WebP up to 2MB)</label>
                            <input type="file" name="logo" accept="image/*"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Or Logo Image URL</label>
                            <input type="url" name="logo_url" value="{{ old('logo_url', $workspace->logo_url) }}" placeholder="https://youragency.com/logo.png"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Primary Brand Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="brand_color" value="{{ old('brand_color', $workspace->brand_color ?? '#dc2626') }}"
                               class="w-12 h-11 p-1 rounded-xl border border-slate-300 cursor-pointer">
                        <input type="text" value="{{ old('brand_color', $workspace->brand_color ?? '#dc2626') }}" readonly
                               class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono text-slate-600">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Secondary Header Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $workspace->secondary_color ?? '#1e293b') }}"
                               class="w-12 h-11 p-1 rounded-xl border border-slate-300 cursor-pointer">
                        <input type="text" value="{{ old('secondary_color', $workspace->secondary_color ?? '#1e293b') }}" readonly
                               class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono text-slate-600">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_whitelabel_enabled" value="1" {{ old('is_whitelabel_enabled', $workspace->is_whitelabel_enabled) ? 'checked' : '' }}
                           class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                    <span class="text-sm font-medium text-slate-700">Enable Whitelabel Mode (Removes "Powered by RedMaple" badge from client PDFs)</span>
                </label>
            </div>
        </div>

        <!-- Card 5: Payment Gateway API Keys -->
        <div id="payments" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-credit-card"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <span>Payment Gateway API Keys (Stripe & PayPal)</span>
                        @if(auth()->user() && auth()->user()->isSuperAdmin())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="ph ph-shield-check"></i> Super Admin Direct Access
                            </span>
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500">Direct client payments deposit directly into your agency merchant account</p>
                </div>
            </div>

            @if(!auth()->user()->isSuperAdmin() && $workspace->isTrial())
                <div class="p-6 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                            <i class="ph ph-lock-key"></i>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-amber-950">Payment Gateways Disabled &bull; Free Trial Evaluation</div>
                            <div class="text-xs text-amber-900 mt-1 leading-relaxed">
                                Stripe and PayPal merchant integrations are disabled during the Free Trial evaluation period. In trial mode, form inputs are completely removed from the page. To connect live merchant accounts and accept client card payments, please upgrade to a paid agency subscription plan.
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('membership.index') }}" class="shrink-0 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 whitespace-nowrap">
                        <i class="ph ph-sparkle"></i>
                        <span>Upgrade to Unlock</span>
                    </a>
                </div>
            @else
                @php
                    $stripeKeys = $workspace->stripe_keys_encrypted ?? [];
                    $paypalKeys = $workspace->paypal_keys_encrypted ?? [];
                @endphp
                <fieldset class="space-y-6">
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                            <i class="ph ph-stripe-logo text-lg text-indigo-600"></i> Stripe Integration
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Publishable Key</label>
                                <input type="text" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $stripeKeys['publishable_key'] ?? '') }}" placeholder="pk_live_..."
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Secret Key</label>
                                <input type="password" name="stripe_secret_key" value="{{ old('stripe_secret_key', !empty($stripeKeys['secret_key']) ? '••••••••••••••••' : '') }}" placeholder="sk_live_..."
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                <i class="ph ph-paypal-logo text-lg text-sky-600"></i> PayPal Integration
                            </h4>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="enable_paypal" value="1" {{ old('enable_paypal', $workspace->enable_paypal ?? true) ? 'checked' : '' }}
                                       class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500">
                                <span class="text-xs font-semibold text-slate-700">Enable PayPal Checkout for Clients</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Client ID</label>
                                <input type="text" name="paypal_client_id" value="{{ old('paypal_client_id', $paypalKeys['client_id'] ?? '') }}" placeholder="Client ID from PayPal Developer"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Secret</label>
                                <input type="password" name="paypal_client_secret" value="{{ old('paypal_client_secret', !empty($paypalKeys['secret']) ? '••••••••••••••••' : '') }}" placeholder="Secret Key"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                    </div>
                </fieldset>
            @endif
        </div>

        <!-- Card 5: SMTP Outgoing Email Delivery Configuration -->
        @php
            $smtp = $workspace->smtp_settings_encrypted ?? [];
            $isTrialUser = (!auth()->user()->isSuperAdmin() && $workspace->isTrial());
            $isSubscribedUser = (!auth()->user()->isSuperAdmin() && !$workspace->isTrial());
            $hasSmtpConfigured = $workspace->hasConfiguredSmtp();
        @endphp
        <div id="smtp" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-envelope-simple"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2 flex-wrap">
                        <span>Outgoing SMTP Mail Server (Hostinger / Custom)</span>
                        @if(auth()->user() && auth()->user()->isSuperAdmin())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="ph ph-shield-check"></i> Super Admin Direct Access
                            </span>
                        @elseif($isTrialUser)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="ph ph-lock-key"></i> Disabled in Free Trial
                            </span>
                        @elseif($hasSmtpConfigured)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="ph ph-check-circle"></i> SMTP Active & Verified
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                <i class="ph ph-warning-circle"></i> Mandatory Setup Required
                            </span>
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500">
                        Configure email delivery credentials for customer invoices and milestone reminders
                        @if($isSubscribedUser && !$hasSmtpConfigured)
                            <span class="text-rose-600 font-bold block mt-0.5"><i class="ph ph-shield-warning"></i> Mandatory Requirement: Subscribed agencies are required to enter authentic Outgoing SMTP credentials before client invoices can be dispatched.</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($isTrialUser)
                <div class="p-6 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                            <i class="ph ph-lock-key"></i>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-amber-950">Outgoing SMTP Disabled &bull; Free Trial Evaluation</div>
                            <div class="text-xs text-amber-900 mt-1 leading-relaxed">
                                Custom outgoing mail server configuration is locked during the Free Trial evaluation period. In trial mode, evaluation emails are dispatched via Invoicy default sandbox routing, and server inputs are completely omitted from the page. To connect your agency's private domain mailbox (Hostinger, Google Workspace, Microsoft 365), please upgrade to a paid agency subscription plan.
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('membership.index') }}" class="shrink-0 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 whitespace-nowrap">
                        <i class="ph ph-sparkle"></i>
                        <span>Upgrade to Unlock</span>
                    </a>
                </div>
            @else
                <fieldset class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                SMTP Host @if($isSubscribedUser) <span class="text-rose-500 font-bold">*</span> @endif
                            </label>
                            <input type="text" name="smtp_host" value="{{ old('smtp_host', $smtp['host'] ?? 'smtp.hostinger.com') }}" placeholder="smtp.hostinger.com"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                SMTP Port @if($isSubscribedUser) <span class="text-rose-500 font-bold">*</span> @endif
                            </label>
                            <input type="number" name="smtp_port" value="{{ old('smtp_port', $smtp['port'] ?? 465) }}" placeholder="465 / 587"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Encryption @if($isSubscribedUser) <span class="text-rose-500 font-bold">*</span> @endif
                            </label>
                            <select name="smtp_encryption" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="ssl" {{ old('smtp_encryption', $smtp['encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                <option value="tls" {{ old('smtp_encryption', $smtp['encryption'] ?? 'ssl') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                SMTP Username / Mailbox Email @if($isSubscribedUser) <span class="text-rose-500 font-bold">*</span> @endif
                            </label>
                            <input type="email" name="smtp_username" value="{{ old('smtp_username', $smtp['username'] ?? '') }}" placeholder="billing@youragency.com"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                SMTP Mailbox Password @if($isSubscribedUser) <span class="text-rose-500 font-bold">*</span> @endif
                            </label>
                            <input type="password" name="smtp_password" value="{{ old('smtp_password', $smtp['password'] ?? '') }}" placeholder="Enter mailbox password..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                From Address @if($isSubscribedUser) <span class="text-rose-500 font-bold">*</span> @endif
                            </label>
                            <input type="email" name="smtp_from_address" value="{{ old('smtp_from_address', $smtp['from_address'] ?? '') }}" placeholder="billing@youragency.com"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                From Display Name @if($isSubscribedUser) <span class="text-rose-500 font-bold">*</span> @endif
                            </label>
                            <input type="text" name="smtp_from_name" value="{{ old('smtp_from_name', $smtp['from_name'] ?? $workspace->name) }}" placeholder="{{ $workspace->name }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        </div>
                    </div>
                </fieldset>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-4">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md transition text-sm">
                <i class="ph ph-floppy-disk text-base"></i> {{ $isTrialUser ? 'Save Workspace Settings' : 'Save Workspace & SMTP Settings' }}
            </button>
        </div>
    </form>

    <!-- Test SMTP Form -->
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 sm:p-8">
        <h4 class="text-sm font-bold text-slate-900 mb-1 flex items-center gap-2">
            <i class="ph ph-paper-plane-tilt text-red-600"></i> Test Live SMTP Email Delivery
            @if($isTrialUser)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                    <i class="ph ph-lock-key"></i> Disabled in Free Trial
                </span>
            @endif
        </h4>
        <p class="text-xs text-slate-500 mb-4">Send an immediate test message to verify your SMTP server authentication</p>
        
        @if($isTrialUser)
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2.5">
                <i class="ph ph-lock-key text-base text-amber-600 shrink-0"></i>
                <span>Live SMTP delivery testing is disabled in Free Trial evaluation. Upgrade to a paid subscription plan to connect and verify your private domain SMTP server.</span>
            </div>
        @else
            <form method="POST" action="{{ route('settings.smtp.test') }}" class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <input type="email" name="test_email" value="{{ auth()->user()->email }}" required placeholder="recipient@example.com"
                       class="w-full sm:max-w-md px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white">
                <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow transition whitespace-nowrap">
                    Send Live Test Email
                </button>
            </form>
        @endif
    </div>
    </div>

    <!-- Card 6: Account Security & Change Password -->
    <div id="security" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 pb-12 scroll-mt-20">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                <i class="ph ph-shield-check"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-base">Account Security & Change Password</h3>
                <p class="text-xs text-slate-500">Update your account login password with enterprise cipher encryption</p>
            </div>
        </div>

        @if($errors->has('current_password') || $errors->has('new_password'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                @error('current_password') <p class="mb-1">{{ $message }}</p> @enderror
                @error('new_password') <p>{{ $message }}</p> @enderror
            </div>
        @endif

        <form method="POST" action="{{ route('settings.password') }}" class="space-y-4 max-w-xl">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Current Password *</label>
                <input type="password" name="current_password" required placeholder="Enter your current password"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">New Password *</label>
                    <input type="password" name="new_password" required placeholder="Min 8 chars, mixed case, symbols"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Confirm New Password *</label>
                    <input type="password" name="new_password_confirmation" required placeholder="Confirm new password"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow transition">
                    <i class="ph ph-lock-key text-base"></i> Update Account Password
                </button>
            </div>
        </form>
    </div>

    <!-- Card 7: Team Members & 5 Seat Access Control -->
    <div id="team" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6 scroll-mt-20">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph ph-users-three"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Team Members & Seat Management</h3>
                    <p class="text-xs text-slate-500">Supervise authorized staff, assign role privileges, and manage workspace seats</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ $teamMembers->count() >= $seatLimit ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-indigo-50 text-indigo-800 border border-indigo-200' }}">
                    <i class="ph ph-user-check text-sm"></i>
                    <span>Seats Allocated: {{ $teamMembers->count() }} / {{ $seatLimit }}</span>
                </span>
            </div>
        </div>

        @if (session('team_success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                <i class="ph ph-check-circle text-base text-emerald-600 shrink-0"></i>
                <span>{{ session('team_success') }}</span>
            </div>
        @endif
        @if (session('team_error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                <i class="ph ph-warning-circle text-base text-rose-600 shrink-0"></i>
                <span>{{ session('team_error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-xs">
                <div class="font-bold flex items-center gap-2">
                    <i class="ph ph-warning-circle text-base text-rose-600 shrink-0"></i>
                    <span>Please correct the errors below:</span>
                </div>
                <ul class="list-disc list-inside pl-4 text-[11px] font-normal space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Role Privileges Guide Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
            <div class="p-3 bg-white rounded-lg border border-slate-200 space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-red-600">
                    <span class="w-2 h-2 rounded-full bg-red-600"></span>
                    <span>Owner (Account Holder)</span>
                </div>
                <p class="text-slate-500 text-[11px] leading-relaxed">
                    Full workspace ownership, subscription upgrades & billing, wire transfer configuration, and adding/removing all team members.
                </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-slate-200 space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-blue-600">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <span>Admin (Agency Manager)</span>
                </div>
                <p class="text-slate-500 text-[11px] leading-relaxed">
                    Create/edit invoices, credit notes, client catalogs, manage service presets, and invite staff members within seat limits.
                </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-slate-200 space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-emerald-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>Staff (Billing Executive)</span>
                </div>
                <p class="text-slate-500 text-[11px] leading-relaxed">
                    Generate client invoices, view service offerings, and record customer payments. Cannot modify workspace settings or delete members.
                </p>
            </div>
        </div>

        <!-- Current Team Members Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Member</th>
                        <th class="py-3 px-4">Role Privileges</th>
                        <th class="py-3 px-4">Email Verification</th>
                        <th class="py-3 px-4">Joined Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($teamMembers as $member)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs shrink-0">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                        {{ $member->name }}
                                        @if($member->id === auth()->id())
                                            <span class="text-[10px] text-slate-400 font-normal">(You)</span>
                                        @endif
                                    </p>
                                    <p class="text-slate-400 text-[11px]">{{ $member->email }}</p>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($member->is_super_admin)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                        <i class="ph ph-shield-check text-xs"></i> Super Admin
                                    </span>
                                @elseif($member->role === 'owner')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
                                        <i class="ph ph-crown text-xs"></i> Owner
                                    </span>
                                @elseif($member->id === auth()->id() || !auth()->user()->isAdmin())
                                    @if($member->role === 'admin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i class="ph ph-shield text-xs"></i> Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="ph ph-user text-xs"></i> Staff
                                        </span>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('settings.team.update_role', $member->id) }}" class="inline-block" onchange="this.submit()">
                                        @csrf
                                        <div class="relative inline-block" title="Change role privileges for {{ $member->name }}">
                                            <select name="role" class="appearance-none text-[11px] font-bold py-1 pl-2.5 pr-7 rounded-lg border {{ $member->role === 'admin' ? 'border-blue-300 bg-blue-50 text-blue-800' : 'border-emerald-300 bg-emerald-50 text-emerald-800' }} hover:border-slate-400 focus:ring-1 focus:ring-red-500 cursor-pointer transition shadow-2xs">
                                                <option value="staff" {{ $member->role === 'staff' ? 'selected' : '' }}>Staff (Invoicing & CRM)</option>
                                                <option value="admin" {{ $member->role === 'admin' ? 'selected' : '' }}>Admin (Full Settings)</option>
                                            </select>
                                            <i class="ph ph-caret-down text-[10px] text-slate-500 absolute right-2 top-2 pointer-events-none"></i>
                                        </div>
                                    </form>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($member->email_verified_at)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Verified on {{ $member->email_verified_at->format('M d, Y H:i') }}">
                                        <i class="ph ph-check-circle text-xs text-emerald-600"></i> Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300" title="Invitation sent, awaiting email activation">
                                        <i class="ph ph-clock text-xs text-amber-600"></i> Pending Activation
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ $member->created_at ? $member->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($member->is_super_admin)
                                    <span class="text-amber-600 text-[11px] font-bold">Platform Super Admin (Protected)</span>
                                @elseif($member->role === 'owner')
                                    <span class="text-slate-400 text-[11px] italic">Owner (Protected)</span>
                                @elseif($member->id === auth()->id())
                                    <span class="text-slate-400 text-[11px] italic">Active Account</span>
                                @else
                                    <div class="inline-flex items-center gap-1">
                                        @if(!$member->email_verified_at)
                                            <form method="POST" action="{{ route('settings.team.resend_verification', $member->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg text-amber-600 hover:text-amber-800 hover:bg-amber-50 transition" title="Resend Email Verification Link">
                                                    <i class="ph ph-paper-plane-tilt text-base"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('settings.team.remove', $member->id) }}" onsubmit="return confirm('Are you sure you want to revoke access and delete user {{ $member->name }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Remove Team Member">
                                                <i class="ph ph-trash text-base"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Add Team Member Form (If seats available) -->
        @if($teamMembers->count() < $seatLimit)
            <div class="pt-6 border-t border-slate-100">
                <div class="mb-4">
                    <h4 class="font-bold text-slate-900 text-sm">Add New Team Member Account</h4>
                    <p class="text-xs text-slate-500">Create an employee account under your workspace within your {{ $seatLimit }}-seat limit. An official email verification &amp; activation link will be automatically dispatched to their inbox.</p>
                </div>

                <form method="POST" action="{{ route('settings.team.add') }}" class="space-y-4" autocomplete="off">
                    @csrf
                    <input type="text" style="display:none" name="fakeusernameremembered" tabindex="-1" autocomplete="off"/>
                    <input type="password" style="display:none" name="fakepasswordremembered" tabindex="-1" autocomplete="off"/>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. John Doe"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" name="email" required placeholder="staff@agency.com" autocomplete="off"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Role Privileges *</label>
                            <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                <option value="staff">Staff (Create invoices & clients)</option>
                                <option value="admin">Admin (Manage invoices & catalog)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Initial Password (Min 8 Chars) *</label>
                            <input type="password" name="password" required placeholder="Temporary password" autocomplete="new-password"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow transition">
                                <i class="ph ph-user-plus text-base"></i> Add Team Member
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="ph ph-info text-base text-indigo-600"></i>
                    <span>All {{ $seatLimit }} seats on your current plan are in use. Upgrade to add more team members.</span>
                </div>
                <a href="{{ route('membership.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs transition shrink-0 text-center">
                    Upgrade Plan for More Seats &rarr;
                </a>
            </div>
        @endif
    </div>

    <!-- Floating Back to Top Button (Item 6) -->
    <div x-data="{ showBackToTop: false }"
         @scroll.window="showBackToTop = (window.pageYOffset > 250)"
         class="fixed bottom-6 right-6 z-40">
        <button x-show="showBackToTop"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-3"
                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                type="button"
                class="p-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-full shadow-2xl hover:shadow-red-600/20 border border-slate-700 transition hover:scale-105 active:scale-95 flex items-center justify-center group"
                title="Back to Top">
            <i class="ph ph-arrow-up text-lg font-bold group-hover:-translate-y-0.5 transition-transform"></i>
        </button>
    </div>

</div>
@endsection
