<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Top Action / Title Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Create Invoice') }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ __('Add customer details, set invoice information and configure recurring billing (if needed).') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs shadow-xs transition">
                {{ __('Cancel') }}
            </a>
            <button type="button" wire:click="requestPublishReview" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                <i class="ph ph-paper-plane-tilt text-sm"></i>
                {{ __('Issue & Publish Invoice') }}
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-600"></i>
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <div class="flex items-center gap-2 font-bold text-rose-900">
                <i class="ph ph-warning-circle text-base text-rose-600"></i>
                {{ __('Please check and correct the following items:') }}
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-[11px] pl-2 text-rose-700">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit.prevent="requestPublishReview">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- ========================================== -->
            <!-- LEFT MAIN COLUMN (lg:col-span-8)           -->
            <!-- ========================================== -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Row 1: Customer Card & Invoice Details Card -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 1. Customer Card (Walkin/CRM button removed as requested) -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    <i class="ph ph-user text-slate-700 text-base"></i>
                                    {{ __('Customer') }}
                                </h3>
                            </div>

                            <div class="space-y-3">
                                <select wire:model.live="clientId" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-700 outline-none">
                                    <option value="">{{ __('Search or select a customer...') }}</option>
                                    <option value="walkin">{{ __('Walk-in / General Customer') }}</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}">{{ $c->company_name ? "{$c->company_name} ({$c->name})" : $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('clientId') <span class="text-rose-600 text-[11px] block">{{ $message }}</span> @enderror

                                @if($isWalkin)
                                    <!-- Walk-in Customer Form -->
                                    <div class="space-y-2.5 bg-slate-50/70 p-3.5 rounded-xl border border-slate-200">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">{{ __('Customer Name') }}</label>
                                            <input type="text" wire:model.live="walkinName" placeholder="{{ __('Customer Name (e.g. ABC Trading Co.)') }}" 
                                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white">
                                        </div>
                                        @error('walkinName') <span class="text-rose-600 text-[11px] block">{{ $message }}</span> @enderror
                                        
                                        @php
                                            $workspace = auth()->user()?->workspace;
                                            $isCanadianWorkspace = $workspace?->isCanadianWorkspace();
                                        @endphp
                                        @if($countryTaxCode === 'CA')
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                                                        {{ __('Point of Sale / Client Province (Place of Supply)') }}
                                                    </label>
                                                    <span class="text-[9px] font-bold text-red-600 bg-red-50 border border-red-200 px-1.5 py-0.5 rounded-full font-mono">
                                                        {{ $walkinProvince }}
                                                    </span>
                                                </div>
                                                <select wire:model.live="walkinProvince" 
                                                        class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white font-semibold text-slate-800">
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
                                                <p class="text-[10px] text-slate-500 mt-0.5">
                                                    {{ __('Sets dynamic provincial tax for retail walk-in point-of-sale.') }}
                                                </p>
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">{{ __('Email address') }} <span class="text-slate-400 font-normal">({{ __('Optional') }})</span></label>
                                                <input type="email" wire:model.live="walkinEmail" placeholder="{{ __('Optional') }}" 
                                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">{{ __('Phone number') }}</label>
                                                <input type="text" wire:model.live="walkinPhone" placeholder="+92 300 1234567" 
                                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                            </div>
                                        </div>
                                    </div>
                                @elseif($selectedClient)
                                    <!-- Selected CRM Client Preview Card -->
                                    <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/50 flex items-start justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 shrink-0">
                                                <i class="ph ph-buildings text-xl"></i>
                                            </div>
                                            <div class="text-xs">
                                                <h4 class="font-bold text-slate-900">{{ $selectedClient->company_name ?: $selectedClient->name }}</h4>
                                                <p class="text-slate-500 mt-0.5">{{ $selectedClient->email ?: 'No email on file' }}</p>
                                                <p class="text-slate-500">{{ $selectedClient->phone ?: 'No phone number' }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('clients.index') }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-red-600 flex items-center gap-1 transition">
                                            <i class="ph ph-pencil-simple text-sm"></i> {{ __('Edit Client') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 2. Invoice Details Card -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="ph ph-file-text text-slate-700 text-base"></i>
                            {{ __('Invoice Details') }}
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">{{ __('Currency') }}</label>
                                <select wire:key="currency-select" 
                                        wire:model.live="currency" 
                                        class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                                    <option value="PKR" @selected($currency === 'PKR')>PKR (Rs) - Pakistani Rupee</option>
                                    <option value="USD" @selected($currency === 'USD')>USD ($) - US Dollar</option>
                                    <option value="AED" @selected($currency === 'AED')>AED (AED) - UAE Dirham</option>
                                    <option value="SAR" @selected($currency === 'SAR')>SAR (SAR) - Saudi Riyal</option>
                                    <option value="EUR" @selected($currency === 'EUR')>EUR (€) - Euro</option>
                                    <option value="GBP" @selected($currency === 'GBP')>GBP (£) - British Pound</option>
                                    <option value="CAD" @selected($currency === 'CAD')>CAD ($) - Canadian Dollar</option>
                                    <option value="AUD" @selected($currency === 'AUD')>AUD ($) - Australian Dollar</option>
                                    <option value="INR" @selected($currency === 'INR')>INR (₹) - Indian Rupee</option>
                                    <option value="TRY" @selected($currency === 'TRY')>TRY (₺) - Turkish Lira</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">{{ __('Document Language') }}</label>
                                <select wire:key="invoice-language-select" 
                                        wire:model.live="language" 
                                        class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                                    <option value="en" @selected($language === 'en')>🇺🇸 English (US/UK)</option>
                                    <option value="ur" @selected($language === 'ur')>🇵🇰 اردو (Urdu)</option>
                                    <option value="ar" @selected($language === 'ar')>🇦🇪 العربية (Arabic)</option>
                                    <option value="tr" @selected($language === 'tr')>🇹🇷 Türkçe (Turkish)</option>
                                    <option value="hi" @selected($language === 'hi')>🇮🇳 हिन्दी (Hindi)</option>
                                    <option value="es" @selected($language === 'es')>🇪🇸 Español (Spanish)</option>
                                    <option value="fr" @selected($language === 'fr')>🇫🇷 Français (French)</option>
                                    <option value="de" @selected($language === 'de')>🇩🇪 Deutsch (German)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">{{ __('Country Tax Rule') }}</label>
                                <select wire:key="tax-country-select" 
                                        wire:model.live="countryTaxCode" 
                                        class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                                    <option value="EXPORT" @selected($countryTaxCode === 'EXPORT')>Zero-Rated Export (0.00%)</option>
                                    <option value="PK" @selected($countryTaxCode === 'PK')>Pakistan - 16% SST</option>
                                    <option value="AE" @selected($countryTaxCode === 'AE')>UAE - 5% VAT</option>
                                    <option value="SA" @selected($countryTaxCode === 'SA')>Saudi Arabia - 15% ZATCA VAT</option>
                                    <option value="GB" @selected($countryTaxCode === 'GB')>United Kingdom - 20% VAT</option>
                                    <option value="US" @selected($countryTaxCode === 'US')>United States - 0% (Sales Tax)</option>
                                    <option value="DE" @selected($countryTaxCode === 'DE')>Germany - 19% MwSt</option>
                                    <option value="FR" @selected($countryTaxCode === 'FR')>France - 20% TVA</option>
                                    <option value="CA" @selected($countryTaxCode === 'CA')>Canada - Dynamic Place-of-Supply (5%-15%)</option>
                                    <option value="AU" @selected($countryTaxCode === 'AU')>Australia - 10% GST</option>
                                    <option value="IN" @selected($countryTaxCode === 'IN')>India - 18% GST</option>
                                    <option value="TR" @selected($countryTaxCode === 'TR')>Turkey - 20% KDV</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">{{ __('Issue Date') }}</label>
                                <input type="date" wire:model.live="issueDate" value="{{ $issueDate }}" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">{{ __('Payment Due Date') }}</label>
                                <input type="date" wire:model.live="dueDate" min="{{ $issueDate }}" value="{{ $dueDate }}" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                            </div>
                        </div>

                        @php
                            $workspace = auth()->user()?->workspace;
                            $isCanadianWorkspace = $workspace?->isCanadianWorkspace();
                        @endphp
                        @if($countryTaxCode === 'CA')
                            <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold text-blue-950 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="ph ph-shield-check text-blue-600 text-sm"></i>
                                        <span>Canadian Place-of-Supply Engine</span>
                                    </label>
                                    <span class="text-xs font-mono font-bold text-blue-800">{{ $taxName }}</span>
                                </div>

                                <!-- Applied Place-of-Supply Rule -->
                                <div class="text-[11px] text-blue-900 font-medium flex items-center gap-1.5">
                                    <i class="ph ph-map-pin text-blue-600 text-xs shrink-0"></i>
                                    <span>
                                        @if($taxCalculationNote)
                                            {{ $taxCalculationNote }}
                                        @else
                                            Place of Supply: {{ $isWalkin ? ($walkinProvince ?: ($workspace?->province ?? 'ON')) : ($selectedClient?->province ?: ($workspace?->province ?? 'ON')) }}
                                        @endif
                                    </span>
                                </div>

                                <!-- Seller's Active Tax Registration Numbers & Badges -->
                                <div class="pt-2 border-t border-blue-200/60 flex flex-wrap items-center gap-2 text-[10px]">
                                    @if($workspace?->small_supplier_exempt)
                                        <span class="px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            CRA Small-Supplier Exempt (0% Tax)
                                        </span>
                                    @else
                                        @if($workspace?->cra_bn_number)
                                            <span class="px-2 py-0.5 rounded-full font-mono font-semibold bg-blue-100 text-blue-900 border border-blue-300">
                                                CRA BN: {{ $workspace->cra_bn_number }}
                                            </span>
                                        @endif
                                        @php
                                            $effectiveProv = $isWalkin ? ($walkinProvince ?: ($workspace?->province ?? 'ON')) : ($selectedClient?->province ?? $workspace?->province ?? 'ON');
                                            $normalizedEffectiveProv = \App\Services\TaxCalculationService::normalizeCanadianProvince($effectiveProv);
                                        @endphp
                                        @if($normalizedEffectiveProv === 'QC' && $workspace?->collect_qst && $workspace?->qst_number)
                                            <span class="px-2 py-0.5 rounded-full font-mono font-semibold bg-indigo-100 text-indigo-900 border border-indigo-300">
                                                QST #: {{ $workspace->qst_number }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @elseif($countryTaxCode === 'EXPORT' || ($isCanadianWorkspace && $selectedClient && ($selectedClient->country_code ?? 'CA') !== 'CA'))
                            <div class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold text-emerald-950 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="ph ph-globe text-emerald-600 text-sm"></i>
                                        <span>CANADIAN EXPORT RULE: Zero-Rated International Supply (0.00%)</span>
                                    </label>
                                    <span class="text-xs font-mono font-bold text-emerald-800">0.00%</span>
                                </div>
                                <div class="text-[11px] text-emerald-900 font-medium flex items-center gap-1.5">
                                    <i class="ph ph-info text-emerald-600 text-xs shrink-0"></i>
                                    <span>Client is outside Canada. Services and supplies delivered to international clients are 0% Zero-Rated under CRA excise tax guidelines.</span>
                                </div>
                                @if($workspace?->cra_bn_number)
                                    <div class="pt-2 border-t border-emerald-200/60 flex items-center gap-2 text-[10px]">
                                        <span class="px-2 py-0.5 rounded-full font-mono font-semibold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                            CRA BN: {{ $workspace->cra_bn_number }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-semibold text-slate-700">{{ __('Applied Tax Rate (%)') }}</label>
                                    <span class="text-xs font-bold text-red-600">{{ $taxName }}</span>
                                </div>
                                <input type="number" step="0.01" wire:model.live.debounce.250ms="taxRate" value="{{ $taxRate }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                            </div>
                        @endif

                        <!-- Future Scheduled Banner -->
                        @if($isScheduled || strtotime($issueDate) > strtotime(date('Y-m-d')))
                            <div class="bg-sky-50/70 border border-sky-100 rounded-xl p-3 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <i class="ph ph-calendar-blank text-sky-600 text-lg"></i>
                                    <div>
                                        <p class="text-[11px] font-bold text-slate-900">{{ __('Future Issue Date Selected') }}</p>
                                        <p class="text-[11px] text-slate-500">{{ date('d/m/Y', strtotime($scheduledPublishDate ?: $issueDate)) }} <span class="cursor-pointer" title="Auto scheduled">&#9432;</span></p>
                                    </div>
                                </div>
                                <div class="text-right flex items-center gap-2.5">
                                    <div class="text-[10px] text-slate-600">
                                        <p class="font-bold text-slate-800">{{ __('Auto-schedule enabled') }}</p>
                                        <p class="text-slate-500">{{ __('Invoice will be sent automatically on 1st of each month.') }}</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="isScheduled" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Row 2: Industry Service Presets (Request 8 - 7 Industries Supported) -->
                <div class="bg-rose-50/40 border border-rose-200/70 rounded-2xl p-4 space-y-3" x-data="{ activeIndustry: '{{ Auth::user()->workspace->business_category ?? 'all' }}' }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-rose-200/50">
                        <div class="flex items-center gap-2 flex-wrap">
                            <i class="ph ph-lightning text-rose-600 text-base"></i>
                            <h4 class="text-xs font-bold text-slate-900">{{ __('1-Click Industry Service Presets') }}</h4>
                            @if(!empty(Auth::user()->workspace->business_category))
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white shadow-2xs">
                                    {{ ucwords(str_replace('_', ' ', Auth::user()->workspace->business_category)) }}
                                </span>
                            @endif
                            <span class="text-[10px] text-slate-500">({{ __('click to add / increase quantity') }})</span>
                        </div>

                        <!-- Industry Tab Filters -->
                        <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 text-[11px] font-bold">
                            <button type="button" @click="activeIndustry = 'all'" :class="activeIndustry === 'all' ? 'bg-red-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-rose-100/60'" class="px-2.5 py-1 rounded-lg border border-rose-200 transition shrink-0">All</button>
                            <button type="button" @click="activeIndustry = 'computer_shop'" :class="activeIndustry === 'computer_shop' ? 'bg-red-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-rose-100/60'" class="px-2.5 py-1 rounded-lg border border-rose-200 transition shrink-0">Computer Shop</button>
                            <button type="button" @click="activeIndustry = 'transportation'" :class="activeIndustry === 'transportation' ? 'bg-red-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-rose-100/60'" class="px-2.5 py-1 rounded-lg border border-rose-200 transition shrink-0">Transportation</button>
                            <button type="button" @click="activeIndustry = 'limousine_taxi'" :class="activeIndustry === 'limousine_taxi' ? 'bg-red-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-rose-100/60'" class="px-2.5 py-1 rounded-lg border border-rose-200 transition shrink-0">Limo & Taxi</button>
                            <button type="button" @click="activeIndustry = 'industrial'" :class="activeIndustry === 'industrial' ? 'bg-red-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-rose-100/60'" class="px-2.5 py-1 rounded-lg border border-rose-200 transition shrink-0">Industrial</button>
                            <button type="button" @click="activeIndustry = 'construction'" :class="activeIndustry === 'construction' ? 'bg-red-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-rose-100/60'" class="px-2.5 py-1 rounded-lg border border-rose-200 transition shrink-0">Construction</button>
                            <button type="button" @click="activeIndustry = 'distributor'" :class="activeIndustry === 'distributor' ? 'bg-red-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-rose-100/60'" class="px-2.5 py-1 rounded-lg border border-rose-200 transition shrink-0">Distributor</button>
                            <button type="button" @click="activeIndustry = 'marketing_agency'" :class="activeIndustry === 'marketing_agency' ? 'bg-red-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-rose-100/60'" class="px-2.5 py-1 rounded-lg border border-rose-200 transition shrink-0">Marketing</button>
                        </div>
                    </div>

                    <!-- Preset Buttons Grouped by Industry -->
                    <div class="flex flex-wrap items-center gap-2 max-h-48 overflow-y-auto pr-1">
                        @foreach($presets as $key => $p)
                            <button type="button"
                                    x-show="activeIndustry === 'all' || activeIndustry === '{{ $p['industry'] ?? 'general' }}'"
                                    wire:click="addPresetItem('{{ $key }}')"
                                    class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-800 font-semibold px-3 py-1.5 rounded-xl border border-rose-200/80 transition shadow-2xs flex items-center gap-1 active:scale-95 group">
                                <span class="text-rose-500 group-hover:text-white font-bold">+</span>
                                <span>{{ $p['name'] }}</span>
                                <span class="text-slate-400 group-hover:text-red-100 font-mono text-[11px]">({{ number_format($p['rate'], 2) }} {{ $currency }}/{{ $p['unit'] }})</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Row 3: Line Items & Services Table -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="ph ph-list-checks text-slate-700 text-base"></i>
                            {{ __('Line Items & Services') }}
                        </h3>
                        <button type="button" wire:click="addItem" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs shadow-xs transition flex items-center gap-1">
                            <i class="ph ph-plus text-xs"></i> {{ __('Add Item') }}
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-y border-slate-100">
                                <tr>
                                    <th class="px-2 py-3 w-8 text-center">#</th>
                                    <th class="px-3 py-3 w-72">{{ __('ITEM / SERVICE') }}</th>
                                    <th class="px-2 py-3 w-16 text-center">{{ __('QTY') }}</th>
                                    <th class="px-2 py-3 w-24">{{ __('UNIT') }}</th>
                                    <th class="px-3 py-3 w-28 text-right">{{ __('RATE') }} ({{ $currency }})</th>
                                    <th class="px-2 py-3 w-20 text-center">{{ __('TAX') }}</th>
                                    <th class="px-3 py-3 w-28 text-right">{{ __('AMOUNT') }}</th>
                                    <th class="px-2 py-3 w-16 text-center">{{ __('ACTIONS') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($items as $idx => $item)
                                    <tr wire:key="line-row-{{ $idx }}" class="hover:bg-slate-50/40 transition">
                                        <td class="px-2 py-4 text-center font-bold text-slate-400 align-top">
                                            {{ $idx + 1 }}
                                        </td>
                                        <!-- Item / Service Autofill & Description -->
                                        <td class="px-3 py-3 space-y-1.5 align-top">
                                            <select wire:key="service-select-{{ $idx }}"
                                                    wire:model.live="items.{{ $idx }}.service_id"
                                                    class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-800 focus:ring-1 focus:ring-red-500">
                                                <option value="">-- {{ __('Select Catalog Service or Preset') }} --</option>
                                                @php
                                                    $industriesList = [
                                                        'computer_shop' => 'Computer Shop Presets',
                                                        'transportation' => 'Transportation Presets',
                                                        'limousine_taxi' => 'Limousine & Taxi Presets',
                                                        'industrial' => 'Industrial Presets',
                                                        'construction' => 'Construction Presets',
                                                        'distributor' => 'Distributor Presets',
                                                        'marketing_agency' => 'Marketing Agency Presets',
                                                        'general' => 'General / Core Presets',
                                                    ];
                                                @endphp
                                                @foreach($industriesList as $indKey => $indLabel)
                                                    <optgroup label="{{ $indLabel }}">
                                                        @foreach($presets as $pKey => $pVal)
                                                            @if(($pVal['industry'] ?? 'general') === $indKey)
                                                                <option value="preset_{{ $pKey }}" @selected(($item['service_id'] ?? '') === 'preset_' . $pKey)>{{ $pVal['name'] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                                @if($services->count() > 0)
                                                    <optgroup label="{{ __('Agency Catalog Services') }}">
                                                        @foreach($services as $s)
                                                            <option value="{{ $s->id }}" @selected(($item['service_id'] ?? '') == $s->id)>{{ $s->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                            <textarea wire:key="desc-input-{{ $idx }}"
                                                      wire:model.live.debounce.250ms="items.{{ $idx }}.description" 
                                                      rows="2" 
                                                      placeholder="{{ __('Description of service rendered (optional)...') }}"
                                                      class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:ring-1 focus:ring-red-500">{{ $item['description'] }}</textarea>
                                            @error('items.' . $idx . '.description')
                                                <span class="text-rose-600 text-[10px] block font-semibold">{{ $message }}</span>
                                            @enderror
                                        </td>

                                        <!-- Quantity (Strict Integer, reactive) -->
                                        <td class="px-2 py-3 align-top">
                                            <input type="number" step="1" min="1" 
                                                   wire:key="qty-input-{{ $idx }}"
                                                   wire:model.live.debounce.250ms="items.{{ $idx }}.quantity"
                                                   value="{{ $item['quantity'] ?? 1 }}"
                                                   class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs text-center font-semibold text-slate-800 focus:ring-1 focus:ring-red-500">
                                            @error('items.' . $idx . '.quantity')
                                                <span class="text-rose-600 text-[10px] block font-semibold">{{ $message }}</span>
                                            @enderror
                                        </td>

                                        <!-- Unit (Reactive & Synced with Preset / Service) -->
                                        <td class="px-2 py-3 align-top">
                                            <select wire:key="unit-select-{{ $idx }}" 
                                                    wire:model.live="items.{{ $idx }}.unit" 
                                                    class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 bg-white">
                                                <option value="Project" @selected(($item['unit'] ?? '') === 'Project')>Project</option>
                                                <option value="Hour" @selected(($item['unit'] ?? '') === 'Hour')>Hour</option>
                                                <option value="Month" @selected(($item['unit'] ?? '') === 'Month')>Month</option>
                                                <option value="Day" @selected(($item['unit'] ?? '') === 'Day')>Day</option>
                                                <option value="Item" @selected(($item['unit'] ?? '') === 'Item')>Item</option>
                                                <option value="Package" @selected(($item['unit'] ?? '') === 'Package')>Package</option>
                                                <option value="Unit" @selected(($item['unit'] ?? '') === 'Unit')>Unit</option>
                                                <option value="Year" @selected(($item['unit'] ?? '') === 'Year')>Year</option>
                                            </select>
                                        </td>

                                        <!-- Rate -->
                                        <td class="px-3 py-3 align-top">
                                            <input type="number" step="0.01" 
                                                   wire:key="rate-input-{{ $idx }}"
                                                   wire:model.live.debounce.250ms="items.{{ $idx }}.rate"
                                                   value="{{ $item['rate'] ?? '0.00' }}"
                                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-right font-semibold text-slate-800 focus:ring-1 focus:ring-red-500">
                                            @error('items.' . $idx . '.rate')
                                                <span class="text-rose-600 text-[10px] block font-semibold">{{ $message }}</span>
                                            @enderror
                                        </td>

                                        <!-- Tax Rate -->
                                        <td class="px-2 py-3 align-top">
                                            <select wire:key="tax-select-{{ $idx }}" 
                                                    wire:model.live="taxRate" 
                                                    class="w-full px-1.5 py-1.5 rounded-lg border border-slate-200 text-xs text-center text-slate-700 bg-white">
                                                <option value="0">0%</option>
                                                <option value="5">5%</option>
                                                <option value="10">10%</option>
                                                <option value="16">16%</option>
                                                <option value="20">20%</option>
                                            </select>
                                        </td>

                                        <!-- Amount (Computed) -->
                                        <td class="px-3 py-4 text-right font-bold text-slate-900 text-xs align-top">
                                            {{ number_format($item['amount'], 2) }}
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-2 py-4 text-center align-top space-x-1.5">
                                            <button type="button" wire:click="removeItem({{ $idx }})" class="text-slate-400 hover:text-rose-600 transition p-1" title="Delete Row">
                                                <i class="ph ph-trash text-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @error('items')
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-semibold">
                            {{ $message }}
                        </div>
                    @enderror

                    <div>
                        <button type="button" wire:click="addItem" class="px-3 py-1.5 rounded-xl border border-dashed border-slate-300 text-slate-600 hover:border-red-500 hover:text-red-600 text-xs font-semibold transition flex items-center gap-1.5">
                            <i class="ph ph-plus text-xs"></i> {{ __('Add Another Item') }}
                        </button>
                    </div>
                </div>

                <!-- Row 4: Invoice Options -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="ph ph-shield text-slate-700 text-base"></i>
                        {{ __('Invoice Options') }}
                    </h3>

                    <!-- 3-Column Feature Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Option 1: Direct Email -->
                        <div class="border border-slate-200 rounded-xl p-4 {{ !auth()->user()->hasVerifiedEmail() ? 'bg-amber-50/40 border-amber-200' : 'bg-slate-50/40' }} relative">
                            <div class="flex items-start justify-between">
                                <i class="ph ph-envelope-simple text-slate-700 text-lg"></i>
                                @if(!auth()->user()->hasVerifiedEmail())
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-300">
                                        <i class="ph ph-warning-circle"></i> Verification Required
                                    </span>
                                @else
                                    <input type="checkbox" wire:model.live="sendEmailToClient" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 cursor-pointer">
                                @endif
                            </div>
                            <h4 class="font-bold text-slate-900 text-xs mt-3">{{ __('Email Invoice to Customer on Publish') }}</h4>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                @if(!auth()->user()->hasVerifiedEmail())
                                    <span class="text-amber-800 font-semibold">{{ __('Account email verification is required before sending invoices directly to client inboxes.') }}</span>
                                @else
                                    {{ __('Automatically dispatches the invoice with direct portal & PDF download link to the client\'s registered email address in CRM.') }}
                                @endif
                            </p>
                        </div>

                        <!-- Option 2: Password-Protected PDF -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/40 relative">
                            <div class="flex items-start justify-between">
                                <i class="ph ph-lock-key text-slate-700 text-lg"></i>
                                <input type="checkbox" wire:model.live="isPdfPasswordProtected" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 cursor-pointer">
                            </div>
                            <h4 class="font-bold text-slate-900 text-xs mt-3">{{ __('Password-Protected Customer PDF') }}</h4>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                {{ __('Recipient must enter a password to open or view the downloaded PDF file.') }}
                            </p>
                            @if($isPdfPasswordProtected)
                                <div class="mt-2.5">
                                    <input type="password" wire:model.live="pdfPassword" placeholder="Enter secure password..."
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-red-300 text-xs bg-white focus:ring-1 focus:ring-red-500">
                                </div>
                            @endif
                        </div>

                        <!-- Option 3: Future Invoice -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/40 relative">
                            <div class="flex items-start justify-between">
                                <i class="ph ph-calendar text-slate-700 text-lg"></i>
                                <input type="checkbox" wire:model.live="isScheduled" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 cursor-pointer">
                            </div>
                            <h4 class="font-bold text-slate-900 text-xs mt-3">{{ __('Plan Future Invoice (Automate Scheduler)') }}</h4>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                {{ __('Draft this invoice today to automatically publish and dispatch to customer on a planned future date.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Late Payment Surcharge Policy Banner -->
                    <div class="border border-rose-100 bg-rose-50/30 rounded-xl p-4">
                        <div class="flex items-start justify-between cursor-pointer" wire:click="$toggle('hasLateSurcharge')">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" wire:model.live="hasLateSurcharge" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 mt-0.5">
                                <div>
                                    <h4 class="font-bold text-red-600 text-xs">{{ __('Late Payment Surcharge Policy (Overdue Protection)') }}</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ __('Automatically calculate and enforce penalty surcharge if customer exceeds the payment grace period.') }}</p>
                                </div>
                            </div>
                            <i class="ph ph-caret-right text-slate-400 text-sm transition {{ $hasLateSurcharge ? 'rotate-90' : '' }}"></i>
                        </div>

                        @if($hasLateSurcharge)
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 mt-3 border-t border-rose-200/60">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Surcharge Type') }}</label>
                                    <select wire:model.live="lateSurchargeType" class="w-full px-2.5 py-1.5 rounded-lg border border-rose-200 text-xs bg-white">
                                        <option value="percent">{{ __('Percentage (%)') }}</option>
                                        <option value="fixed">{{ __('Fixed Flat Fee') }} ({{ $currency }})</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Fee Amount / %') }}</label>
                                    <input type="number" step="0.01" min="0" wire:model.live="lateSurchargeValue"
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-rose-200 text-xs bg-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">{{ __('Grace Period (Days Overdue)') }}</label>
                                    <input type="number" step="1" min="1" wire:model.live="lateSurchargeDays" placeholder="7"
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-rose-200 text-xs bg-white">
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Customer Notes & Terms Side-by-Side -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-slate-700">{{ __('Customer Notes / Reference') }}</label>
                            </div>
                            <textarea wire:model.live.debounce.300ms="notes" rows="3" maxlength="500" placeholder="e.g. Thank you for your business! Please reference invoice number when paying."
                                      class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:ring-1 focus:ring-red-500 bg-white"></textarea>
                            <div class="text-right text-[10px] text-slate-400 mt-0.5">
                                {{ strlen($notes) }}/500
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-slate-700">{{ __('Terms & Payment Instructions') }}</label>
                            </div>
                            <textarea wire:model.live.debounce.300ms="terms" rows="3" maxlength="500" placeholder="Payment is due within the specified payment terms. Please remit payment via the portal link or wire details below."
                                      class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:ring-1 focus:ring-red-500 bg-white"></textarea>
                            <div class="text-right text-[10px] text-slate-400 mt-0.5">
                                {{ strlen($terms) }}/500
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- RIGHT SIDEBAR (lg:col-span-4)              -->
            <!-- ========================================== -->
            <div class="lg:col-span-4 space-y-6">
                <!-- 1. Financial Summary Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="ph ph-receipt text-slate-700 text-base"></i>
                        {{ __('Financial Summary') }}
                    </h3>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">{{ __('Subtotal') }}</span>
                            <span class="font-bold text-slate-900">{{ number_format($subtotal, 2) }} {{ $currency }}</span>
                        </div>

                        <!-- Pre-tax discount -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">{{ __('Pre-Tax Discount') }}</span>
                            <div class="flex items-center gap-1.5">
                                <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="discountAmount" placeholder="0"
                                       class="w-24 text-right px-2 py-1 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-1 focus:ring-red-500 bg-white">
                                <span class="text-[11px] text-slate-500 font-semibold">{{ $currency }}</span>
                            </div>
                        </div>

                        <!-- Promo / Coupon Code Section (Items 4 & 5) -->
                        <div class="pt-2 border-t border-slate-100">
                            @if($couponApplied)
                                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-800 space-y-1">
                                    <div class="flex items-center justify-between font-bold">
                                        <span class="flex items-center gap-1">
                                            <i class="ph ph-ticket text-emerald-600 text-sm"></i> {{ $appliedCouponCode }}
                                        </span>
                                        <button type="button" wire:click="removeCoupon" class="text-rose-600 hover:underline text-[10px] font-semibold">
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                    <p class="text-slate-600 text-[10px]">{{ $couponMessage }}</p>
                                </div>
                            @else
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <div class="relative flex-1">
                                            <input type="text" wire:model="couponCode" placeholder="{{ __('Promo / Coupon Code') }}"
                                                   class="w-full pl-7 pr-2 py-1.5 rounded-lg border border-slate-200 text-xs font-mono uppercase focus:ring-1 focus:ring-red-500 bg-white">
                                            <i class="ph ph-ticket absolute left-2 top-2 text-slate-400 text-xs"></i>
                                        </div>
                                        <button type="button" wire:click="applyCoupon" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow-xs transition">
                                            {{ __('Apply') }}
                                        </button>
                                    </div>
                                    @error('couponCode')
                                        <span class="text-rose-600 text-[10px] block">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        @if($isDualTax)
                            <div class="space-y-1.5 pt-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">{{ $federalTaxName }} ({{ number_format($federalTaxRate, 3) }}%)</span>
                                    <span class="font-bold text-slate-900">{{ number_format($federalTaxAmount, 2) }} {{ $currency }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">{{ $provincialTaxName }} ({{ number_format($provincialTaxRate, 3) }}%)</span>
                                    <span class="font-bold text-slate-900">{{ number_format($provincialTaxAmount, 2) }} {{ $currency }}</span>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-medium">{{ $taxName }} ({{ number_format($taxRate, 2) }}%)</span>
                                <span class="font-bold text-slate-900">{{ number_format($taxAmount, 2) }} {{ $currency }}</span>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-base font-bold text-slate-900">{{ __('Total Due') }}</span>
                            <span class="text-xl font-black text-red-600">{{ number_format($totalAmount, 2) }} {{ $currency }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-3 space-y-2.5">
                        <button type="button" wire:click="requestPublishReview" class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center justify-center gap-2">
                            <i class="ph ph-paper-plane-tilt text-sm"></i>
                            {{ __('Issue & Publish Invoice') }}
                        </button>
                        <button type="button" wire:click="saveInvoice('draft')" class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl text-xs shadow-2xs transition flex items-center justify-center gap-2">
                            <i class="ph ph-folder text-sm text-slate-500"></i>
                            {{ __('Save as Draft') }}
                        </button>
                    </div>
                </div>

                <!-- 2. Recurring Billing Card (Exact match to Mockup) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="ph ph-arrows-clockwise text-slate-700 text-base"></i>
                            {{ __('Recurring Billing') }}
                        </h3>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:click="toggleRecurring" {{ $isRecurring ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    @if($isRecurring)
                        <div class="space-y-4 pt-1">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                                <i class="ph ph-calendar text-slate-600 text-sm"></i>
                                {{ __('Monthly Subscription') }}
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500">{{ __('Repeat every') }}</span>
                                <input type="number" step="1" min="1" wire:model.live="recurringInterval" class="w-14 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center font-bold">
                                <select wire:model.live="recurringFrequency" class="px-2 py-1 rounded-lg border border-slate-200 text-xs text-slate-700 bg-white">
                                    <option value="month">{{ __('month') }}</option>
                                    <option value="week">{{ __('week') }}</option>
                                    <option value="year">{{ __('year') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">{{ __('Next invoice date') }}</span>
                                <span class="font-bold text-slate-900">{{ date('d/m/Y', strtotime($recurringNextDate)) }}</span>
                            </div>

                            <!-- Toggles -->
                            <div class="space-y-3 pt-2 border-t border-slate-100">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-2">
                                        <i class="ph ph-check-circle text-emerald-500 text-base mt-0.5"></i>
                                        <div class="text-[11px]">
                                            <p class="font-bold text-slate-900">{{ __('Auto email to customer') }}</p>
                                            <p class="text-slate-500">{{ __('Send invoice by email on each cycle.') }}</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="recurringAutoEmail" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>

                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-2">
                                        <i class="ph ph-bell text-amber-500 text-base mt-0.5"></i>
                                        <div class="text-[11px]">
                                            <p class="font-bold text-slate-900">{{ __('Vendor Alert') }}</p>
                                            <p class="text-slate-500">{{ __('Notify you when invoice is sent to customer.') }}</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="recurringVendorAlert" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>
                            </div>

                            <!-- Blue Notice Info Box -->
                            <div class="bg-sky-50/70 border border-sky-100 rounded-xl p-3 flex items-start gap-2.5">
                                <i class="ph ph-envelope-simple text-sky-600 text-base shrink-0 mt-0.5"></i>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    {{ __('This will create a recurring invoice and automatically send it to your customer every month on the 1st.') }}
                                </p>
                            </div>

                            <div class="pt-1">
                                <a href="{{ route('recurring.index') }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-red-600 flex items-center gap-1.5 transition">
                                    <i class="ph ph-calendar-blank text-sm"></i> {{ __('View Schedule') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-500 leading-relaxed">
                            {{ __('Turn on recurring billing to automatically generate and dispatch invoices to this client on a recurring schedule.') }}
                        </p>
                    @endif
                </div>

                <!-- Advertisement Card (Above Need Help?) -->
                @include('components.ad-slot', ['placement' => 'invoice_sidebar'])

                <!-- 3. Need Help? Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-3">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="ph ph-info text-slate-700 text-base"></i>
                        {{ __('Need Help?') }}
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ __('For any questions about invoicing or payment setup, visit our help center or contact support.') }}
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('docs.index') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 hover:text-red-600 transition">
                            {{ __('Visit Help Center') }} &rarr;
                        </a>
                    </div>
                </div>

                <!-- 4. Security & Compliance Footer Badges -->
                <div class="space-y-3.5 pt-2 px-2 text-xs">
                    <div class="flex items-start gap-2.5">
                        <i class="ph ph-currency-circle-dollar text-slate-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <h5 class="font-bold text-slate-900 text-xs">{{ __('Multi-currency Support') }}</h5>
                            <p class="text-[11px] text-slate-500">{{ __('Create invoices in your preferred currency.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ph ph-receipt text-slate-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <h5 class="font-bold text-slate-900 text-xs">{{ __('Automated Tax Calculation') }}</h5>
                            <p class="text-[11px] text-slate-500">{{ __('Supports country specific tax rules.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ph ph-shield-check text-slate-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <h5 class="font-bold text-slate-900 text-xs">{{ __('Secure & Compliant') }}</h5>
                            <p class="text-[11px] text-slate-500">{{ __('Your data is protected with enterprise-grade security.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Pre-Dispatch Review Modal (Item 3) -->
    @if($showReviewModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto animate-in fade-in duration-200">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8 space-y-6">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 border border-red-100 shadow-inner">
                        <i class="ph ph-file-text text-2xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-800">
                                Pre-Dispatch Review
                            </span>
                            <span class="text-xs text-slate-400 font-mono">{{ $currency }}</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 mt-0.5">{{ __('Review Invoice Before Publishing') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Please verify customer recipient and financial terms before issuing.') }}</p>
                    </div>
                </div>
                <button type="button" wire:click="closeReviewModal" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <!-- Client & Meta Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="text-[10px] uppercase font-extrabold text-slate-400 tracking-wider mb-1 flex items-center gap-1.5">
                        <i class="ph ph-user text-slate-500"></i> {{ __('Client Recipient') }}
                    </div>
                    @php
                        $selectedClient = !$isWalkin && $clientId ? \App\Models\Client::find($clientId) : null;
                    @endphp
                    <div class="font-bold text-slate-900 text-sm">
                        {{ $isWalkin ? ($walkinName ?: __('Walk-in Customer')) : ($selectedClient?->company_name ?? $selectedClient?->name ?? __('Client')) }}
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">
                        {{ $isWalkin ? ($walkinEmail ?: __('No email provided')) : ($selectedClient?->email ?? __('No email')) }}
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="text-[10px] uppercase font-extrabold text-slate-400 tracking-wider mb-1 flex items-center gap-1.5">
                        <i class="ph ph-calendar text-slate-500"></i> {{ __('Billing Terms') }}
                    </div>
                    <div class="text-xs text-slate-700 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Issue Date') }}:</span>
                            <span class="font-bold text-slate-900">{{ $issueDate ?: date('Y-m-d') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Due Date') }}:</span>
                            <span class="font-bold text-slate-900">{{ $dueDate ?: date('Y-m-d') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table Preview -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden max-h-52 overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="py-2.5 px-4">{{ __('Item') }}</th>
                            <th class="py-2.5 px-3 text-center">{{ __('Qty') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('Rate') }}</th>
                            <th class="py-2.5 px-4 text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $itm)
                            <tr>
                                <td class="py-2 px-4 font-semibold text-slate-900">{{ $itm['description'] ?: __('Item') }}</td>
                                <td class="py-2 px-3 text-center text-slate-600">{{ $itm['quantity'] }} {{ $itm['unit'] ?? '' }}</td>
                                <td class="py-2 px-3 text-right text-slate-600">{{ number_format((float)($itm['rate'] ?? 0), 2) }}</td>
                                <td class="py-2 px-4 text-right font-bold text-slate-900">
                                    {{ number_format(((int)($itm['quantity'] ?? 1)) * ((float)($itm['rate'] ?? 0)), 2) }} {{ $currency }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Totals Summary -->
            <div class="bg-red-50/50 border border-red-100 rounded-2xl p-4 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>{{ __('Subtotal') }}:</span>
                    <span class="font-bold text-slate-900">{{ number_format($subtotal, 2) }} {{ $currency }}</span>
                </div>
                @if($discountAmount > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>{{ __('Discount') }}:</span>
                    <span class="font-bold">-{{ number_format($discountAmount, 2) }} {{ $currency }}</span>
                </div>
                @endif
                @if($isDualTax)
                <div class="flex justify-between text-slate-600">
                    <span>{{ $federalTaxName }} ({{ number_format($federalTaxRate, 3) }}%):</span>
                    <span class="font-bold text-slate-900">+{{ number_format($federalTaxAmount, 2) }} {{ $currency }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>{{ $provincialTaxName }} ({{ number_format($provincialTaxRate, 3) }}%):</span>
                    <span class="font-bold text-slate-900">+{{ number_format($provincialTaxAmount, 2) }} {{ $currency }}</span>
                </div>
                @else
                <div class="flex justify-between text-slate-600">
                    <span>{{ $taxName }} ({{ number_format($taxRate, 2) }}%):</span>
                    <span class="font-bold text-slate-900">+{{ number_format($taxAmount, 2) }} {{ $currency }}</span>
                </div>
                @endif
                <div class="pt-2 border-t border-red-200 flex justify-between items-center text-sm font-black">
                    <span class="text-slate-900">{{ __('Final Total Due') }}:</span>
                    <span class="text-xl text-red-600">{{ number_format($totalAmount, 2) }} {{ $currency }}</span>
                </div>
            </div>

            <!-- Email Notification Preference -->
            <div class="p-4 rounded-2xl border border-slate-200 bg-white">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="sendEmailToClient" class="w-4 h-4 rounded text-red-600 focus:ring-red-500">
                    <div class="text-xs">
                        <span class="font-bold text-slate-900 block">{{ __('Dispatch invoice directly to customer email upon publish') }}</span>
                        <span class="text-slate-500">{{ __('Customer will receive official invoice link and PDF attachment.') }}</span>
                    </div>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 gap-3">
                <button type="button" wire:click="closeReviewModal"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-bold text-xs shadow-2xs transition">
                    <i class="ph ph-arrow-left"></i> {{ __('Back to Edit') }}
                </button>

                <button type="button" wire:click="confirmAndPublish" wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs shadow-md transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="confirmAndPublish">
                        <i class="ph ph-paper-plane-tilt text-sm"></i> {{ __('Confirm & Issue Now') }}
                    </span>
                    <span wire:loading wire:target="confirmAndPublish" class="flex items-center gap-2">
                        <i class="ph ph-spinner animate-spin text-sm"></i> {{ __('Publishing & Sending...') }}
                    </span>
                </button>
            </div>

        </div>
    </div>
    @endif
</div>
