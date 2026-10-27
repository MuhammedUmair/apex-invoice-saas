<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Top Action / Title Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Create Invoice</h1>
            <p class="text-xs text-slate-500 mt-1">Add customer details, set invoice information and configure recurring billing (if needed).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs shadow-xs transition">
                Cancel
            </a>
            <button type="button" wire:click="saveInvoice('issued')" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                <i class="ph ph-paper-plane-tilt text-sm"></i>
                Issue Invoice
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-600"></i>
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="saveInvoice('issued')">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- ========================================== -->
            <!-- LEFT MAIN COLUMN (lg:col-span-8)           -->
            <!-- ========================================== -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Row 1: Customer Card & Invoice Details Card -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 1. Customer Card -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    <i class="ph ph-user text-slate-700 text-base"></i>
                                    Customer
                                </h3>
                                <div class="flex items-center bg-slate-100 p-0.5 rounded-lg text-[10px] font-semibold">
                                    <button type="button" wire:click="disableWalkin" 
                                            class="px-2 py-0.5 rounded transition {{ !$isWalkin ? 'bg-white shadow-xs text-slate-900 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                                        CRM
                                    </button>
                                    <button type="button" wire:click="enableWalkin" 
                                            class="px-2 py-0.5 rounded transition {{ $isWalkin ? 'bg-red-600 text-white font-bold shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                                        Walk-in
                                    </button>
                                </div>
                            </div>

                            @if(!$isWalkin)
                                <div class="space-y-3">
                                    <select wire:model.live="clientId" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-700 outline-none">
                                        <option value="">Search or select a customer...</option>
                                        @foreach($clients as $c)
                                            <option value="{{ $c->id }}">{{ $c->company_name ? "{$c->company_name} ({$c->name})" : $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('clientId') <span class="text-rose-600 text-[11px] block">{{ $message }}</span> @enderror

                                    @if($this->selectedClient)
                                        <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/50 flex items-start justify-between">
                                            <div class="flex items-start gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 shrink-0">
                                                    <i class="ph ph-buildings text-xl"></i>
                                                </div>
                                                <div class="text-xs">
                                                    <h4 class="font-bold text-slate-900">{{ $this->selectedClient->company_name ?: $this->selectedClient->name }}</h4>
                                                    <p class="text-slate-500 mt-0.5">{{ $this->selectedClient->email ?: 'No email on file' }}</p>
                                                    <p class="text-slate-500">{{ $this->selectedClient->phone ?: 'No phone number' }}</p>
                                                </div>
                                            </div>
                                            <a href="{{ route('clients.index') }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-red-600 flex items-center gap-1 transition">
                                                <i class="ph ph-pencil-simple text-sm"></i> Edit
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="space-y-2 bg-slate-50/60 p-3 rounded-xl border border-red-200">
                                    <input type="text" wire:model.live="walkinName" placeholder="Customer Name (e.g. ABC Trading Co.)" 
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white">
                                    @error('walkinName') <span class="text-rose-600 text-[11px] block">{{ $message }}</span> @enderror
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="email" wire:model.live="walkinEmail" placeholder="Email address" 
                                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                        <input type="text" wire:model.live="walkinPhone" placeholder="Phone number" 
                                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Invoice Details Card -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="ph ph-file-text text-slate-700 text-base"></i>
                            Invoice Details
                        </h3>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Currency</label>
                                <select wire:model.live="currency" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                                    <option value="PKR">PKR (Rs) - Pakistani Rupee</option>
                                    <option value="USD">USD ($) - US Dollar</option>
                                    <option value="AED">AED (AED) - UAE Dirham</option>
                                    <option value="SAR">SAR (SAR) - Saudi Riyal</option>
                                    <option value="EUR">EUR (€) - Euro</option>
                                    <option value="GBP">GBP (£) - British Pound</option>
                                    <option value="CAD">CAD ($) - Canadian Dollar</option>
                                    <option value="AUD">AUD ($) - Australian Dollar</option>
                                    <option value="INR">INR (₹) - Indian Rupee</option>
                                    <option value="TRY">TRY (₺) - Turkish Lira</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Country Tax Rule</label>
                                <select wire:model.live="countryTaxCode" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                                    <option value="PK">Pakistan - 16% SST</option>
                                    <option value="AE">UAE - 5% VAT</option>
                                    <option value="SA">Saudi Arabia - 15% ZATCA VAT</option>
                                    <option value="GB">United Kingdom - 20% VAT</option>
                                    <option value="US">United States - 0% (Sales Tax)</option>
                                    <option value="DE">Germany - 19% MwSt</option>
                                    <option value="FR">France - 20% TVA</option>
                                    <option value="CA">Canada - 13% GST/HST</option>
                                    <option value="AU">Australia - 10% GST</option>
                                    <option value="IN">India - 18% GST</option>
                                    <option value="TR">Turkey - 20% KDV</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Issue Date</label>
                                <div class="relative">
                                    <input type="date" wire:model.live="issueDate" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Due Date</label>
                                <div class="relative">
                                    <input type="date" wire:model.live="dueDate" min="{{ $issueDate }}" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-semibold text-slate-700">Applied Tax Rate (%)</label>
                                <span class="text-xs font-bold text-red-600">{{ $taxName }}</span>
                            </div>
                            <input type="number" step="0.01" wire:model.live.debounce.250ms="taxRate" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-red-500 bg-white text-slate-800">
                        </div>

                        <!-- Future Scheduled Banner (From Mockup) -->
                        @if($isScheduled || strtotime($issueDate) > strtotime(date('Y-m-d')))
                            <div class="bg-sky-50/70 border border-sky-100 rounded-xl p-3 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <i class="ph ph-calendar-blank text-sky-600 text-lg"></i>
                                    <div>
                                        <p class="text-[11px] font-bold text-slate-900">Future Issue Date Selected</p>
                                        <p class="text-[11px] text-slate-500">{{ date('d/m/Y', strtotime($scheduledPublishDate ?: $issueDate)) }} <span class="cursor-pointer" title="Automatically scheduled">&#9432;</span></p>
                                    </div>
                                </div>
                                <div class="text-right flex items-center gap-2.5">
                                    <div class="text-[10px] text-slate-600">
                                        <p class="font-bold text-slate-800">Auto-schedule enabled</p>
                                        <p class="text-slate-500">Invoice will be sent automatically on 1st of each month.</p>
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

                <!-- Row 2: Quick Presets (Soft Rose / Red-50 container) -->
                <div class="bg-rose-50/30 border border-rose-100 rounded-2xl p-4">
                    <div class="flex items-center gap-2 mb-2.5">
                        <i class="ph ph-lightning text-rose-500 text-base"></i>
                        <h4 class="text-xs font-bold text-slate-800">Quick Presets</h4>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="addPresetItem('consulting')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-3 py-1.5 rounded-xl border border-rose-200/70 transition shadow-2xs flex items-center gap-1">
                            <span class="text-rose-500 font-bold">+</span> Consulting ($100/hr)
                        </button>
                        <button type="button" wire:click="addPresetItem('web_dev')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-3 py-1.5 rounded-xl border border-rose-200/70 transition shadow-2xs flex items-center gap-1">
                            <span class="text-rose-500 font-bold">+</span> Web Design ($750)
                        </button>
                        <button type="button" wire:click="addPresetItem('retainer')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-3 py-1.5 rounded-xl border border-rose-200/70 transition shadow-2xs flex items-center gap-1">
                            <span class="text-rose-500 font-bold">+</span> Monthly Retainer ($500)
                        </button>
                        <button type="button" wire:click="addPresetItem('labor')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-3 py-1.5 rounded-xl border border-rose-200/70 transition shadow-2xs flex items-center gap-1">
                            <span class="text-rose-500 font-bold">+</span> Technical Labor ($75/hr)
                        </button>
                        <button type="button" wire:click="addPresetItem('marketing')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-3 py-1.5 rounded-xl border border-rose-200/70 transition shadow-2xs flex items-center gap-1">
                            <span class="text-rose-500 font-bold">+</span> Marketing ($250)
                        </button>
                        <button type="button" wire:click="addPresetItem('product')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-3 py-1.5 rounded-xl border border-rose-200/70 transition shadow-2xs flex items-center gap-1">
                            <span class="text-rose-500 font-bold">+</span> Merchandise ($45)
                        </button>
                    </div>
                </div>

                <!-- Row 3: Line Items & Services Table -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="ph ph-list-checks text-slate-700 text-base"></i>
                            Line Items & Services
                        </h3>
                        <button type="button" wire:click="addItem" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs shadow-xs transition flex items-center gap-1">
                            <i class="ph ph-plus text-xs"></i> Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-y border-slate-100">
                                <tr>
                                    <th class="px-2 py-3 w-8 text-center">#</th>
                                    <th class="px-3 py-3 w-72">ITEM / SERVICE</th>
                                    <th class="px-2 py-3 w-16 text-center">QTY</th>
                                    <th class="px-2 py-3 w-24">UNIT</th>
                                    <th class="px-3 py-3 w-28 text-right">RATE ({{ $currency }})</th>
                                    <th class="px-2 py-3 w-20 text-center">TAX</th>
                                    <th class="px-3 py-3 w-28 text-right">AMOUNT</th>
                                    <th class="px-2 py-3 w-16 text-center">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($items as $idx => $item)
                                    <tr class="hover:bg-slate-50/40 transition">
                                        <td class="px-2 py-4 text-center font-bold text-slate-400 align-top">
                                            {{ $idx + 1 }}
                                        </td>
                                        <!-- Item / Service Autofill & Description -->
                                        <td class="px-3 py-3 space-y-1.5 align-top">
                                            <select wire:change="selectService({{ $idx }}, $event.target.value)" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-800 focus:ring-1 focus:ring-red-500">
                                                <option value="">-- Select Catalog Service or Custom --</option>
                                                @foreach($services as $s)
                                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                            <textarea wire:model.live.debounce.250ms="items.{{ $idx }}.description" rows="2" placeholder="Design, development and deployment of company website."
                                                      class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:ring-1 focus:ring-red-500"></textarea>
                                        </td>

                                        <!-- Quantity (Strict Integer) -->
                                        <td class="px-2 py-3 align-top">
                                            <input type="number" step="1" min="1" wire:model.live.debounce.250ms="items.{{ $idx }}.quantity"
                                                   class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs text-center font-semibold text-slate-800 focus:ring-1 focus:ring-red-500">
                                        </td>

                                        <!-- Unit -->
                                        <td class="px-2 py-3 align-top">
                                            <select wire:model.live.debounce.250ms="items.{{ $idx }}.unit" class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 bg-white">
                                                <option value="Project">Project</option>
                                                <option value="Month">Month</option>
                                                <option value="Hour">Hour</option>
                                                <option value="Day">Day</option>
                                                <option value="Unit">Unit</option>
                                                <option value="Item">Item</option>
                                                <option value="Package">Package</option>
                                            </select>
                                        </td>

                                        <!-- Rate -->
                                        <td class="px-3 py-3 align-top">
                                            <input type="number" step="0.01" wire:model.live.debounce.250ms="items.{{ $idx }}.rate"
                                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-right font-semibold text-slate-800 focus:ring-1 focus:ring-red-500">
                                        </td>

                                        <!-- Tax Rate -->
                                        <td class="px-2 py-3 align-top">
                                            <select wire:model.live.debounce.250ms="taxRate" class="w-full px-1.5 py-1.5 rounded-lg border border-slate-200 text-xs text-center text-slate-700 bg-white">
                                                <option value="{{ $taxRate }}">{{ number_format($taxRate, 0) }}%</option>
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

                    <div>
                        <button type="button" wire:click="addItem" class="px-3 py-1.5 rounded-xl border border-dashed border-slate-300 text-slate-600 hover:border-red-500 hover:text-red-600 text-xs font-semibold transition flex items-center gap-1.5">
                            <i class="ph ph-plus text-xs"></i> Add Another Item
                        </button>
                    </div>
                </div>

                <!-- Row 4: Invoice Options -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="ph ph-shield text-slate-700 text-base"></i>
                        Invoice Options
                    </h3>

                    <!-- 3-Column Feature Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Option 1: Direct Email -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/40 relative">
                            <div class="flex items-start justify-between">
                                <i class="ph ph-envelope-simple text-slate-700 text-lg"></i>
                                <input type="checkbox" wire:model.live="sendEmailToClient" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 cursor-pointer">
                            </div>
                            <h4 class="font-bold text-slate-900 text-xs mt-3">Email Invoice to Customer on Publish</h4>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Automatically dispatches the invoice with direct portal & PDF download link to the client's registered email address in CRM.
                            </p>
                        </div>

                        <!-- Option 2: Password-Protected PDF -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/40 relative">
                            <div class="flex items-start justify-between">
                                <i class="ph ph-lock-key text-slate-700 text-lg"></i>
                                <input type="checkbox" wire:model.live="isPdfPasswordProtected" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 cursor-pointer">
                            </div>
                            <h4 class="font-bold text-slate-900 text-xs mt-3">Password-Protected Customer PDF</h4>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Recipient must enter a password to open or view the downloaded PDF file.
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
                            <h4 class="font-bold text-slate-900 text-xs mt-3">Plan Future Invoice (Automate Scheduler)</h4>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Draft this invoice today to automatically publish and dispatch to customer on a planned future date.
                            </p>
                        </div>
                    </div>

                    <!-- Late Payment Surcharge Policy Banner -->
                    <div class="border border-rose-100 bg-rose-50/30 rounded-xl p-4">
                        <div class="flex items-start justify-between cursor-pointer" wire:click="$toggle('hasLateSurcharge')">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" wire:model.live="hasLateSurcharge" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 mt-0.5">
                                <div>
                                    <h4 class="font-bold text-red-600 text-xs">Late Payment Surcharge Policy (Overdue Protection)</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Automatically calculate and enforce penalty surcharge if customer exceeds the payment grace period.</p>
                                </div>
                            </div>
                            <i class="ph ph-caret-right text-slate-400 text-sm transition {{ $hasLateSurcharge ? 'rotate-90' : '' }}"></i>
                        </div>

                        @if($hasLateSurcharge)
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 mt-3 border-t border-rose-200/60">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Surcharge Type</label>
                                    <select wire:model.live="lateSurchargeType" class="w-full px-2.5 py-1.5 rounded-lg border border-rose-200 text-xs bg-white">
                                        <option value="percent">Percentage (%)</option>
                                        <option value="fixed">Fixed Flat Fee ({{ $currency }})</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Fee Amount / %</label>
                                    <input type="number" step="0.01" min="0" wire:model.live="lateSurchargeValue"
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-rose-200 text-xs bg-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Grace Period (Days Overdue)</label>
                                    <input type="number" step="1" min="1" wire:model.live="lateSurchargeDays" placeholder="7"
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-rose-200 text-xs bg-white">
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Customer Notes & Terms Side-by-Side (from Mockup) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-slate-700">Customer Notes / Reference</label>
                            </div>
                            <textarea wire:model.live.debounce.300ms="notes" rows="3" maxlength="500" placeholder="e.g. Thank you for your business! Please reference invoice number when paying."
                                      class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:ring-1 focus:ring-red-500 bg-white"></textarea>
                            <div class="text-right text-[10px] text-slate-400 mt-0.5">
                                {{ strlen($notes) }}/500
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-slate-700">Terms & Payment Instructions</label>
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
                        Financial Summary
                    </h3>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Subtotal</span>
                            <span class="font-bold text-slate-900">{{ number_format($subtotal, 2) }} {{ $currency }}</span>
                        </div>

                        <!-- Pre-tax discount -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Pre-Tax Discount</span>
                            <div class="flex items-center gap-1.5">
                                <input type="number" step="0.01" min="0" wire:model.live.debounce.250ms="discountAmount" placeholder="0"
                                       class="w-24 text-right px-2 py-1 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-1 focus:ring-red-500 bg-white">
                                <span class="text-[11px] text-slate-500 font-semibold">{{ $currency }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">{{ $taxName }} ({{ number_format($taxRate, 2) }}%)</span>
                            <span class="font-bold text-slate-900">{{ number_format($taxAmount, 2) }} {{ $currency }}</span>
                        </div>

                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-base font-bold text-slate-900">Total Due</span>
                            <span class="text-xl font-black text-red-600">{{ number_format($totalAmount, 2) }} {{ $currency }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-3 space-y-2.5">
                        <button type="submit" class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center justify-center gap-2">
                            <i class="ph ph-paper-plane-tilt text-sm"></i>
                            Issue & Publish Invoice
                        </button>
                        <button type="button" wire:click="saveInvoice('draft')" class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl text-xs shadow-2xs transition flex items-center justify-center gap-2">
                            <i class="ph ph-folder text-sm text-slate-500"></i>
                            Save as Draft
                        </button>
                    </div>
                </div>

                <!-- 2. Recurring Billing Card (Exact match to Mockup) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="ph ph-arrows-clockwise text-slate-700 text-base"></i>
                            Recurring Billing
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
                                Monthly Subscription
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500">Repeat every</span>
                                <input type="number" step="1" min="1" wire:model.live="recurringInterval" class="w-14 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center font-bold">
                                <select wire:model.live="recurringFrequency" class="px-2 py-1 rounded-lg border border-slate-200 text-xs text-slate-700 bg-white">
                                    <option value="month">month</option>
                                    <option value="week">week</option>
                                    <option value="year">year</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Next invoice date</span>
                                <span class="font-bold text-slate-900">{{ date('d/m/Y', strtotime($recurringNextDate)) }}</span>
                            </div>

                            <!-- Toggles -->
                            <div class="space-y-3 pt-2 border-t border-slate-100">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-2">
                                        <i class="ph ph-check-circle text-emerald-500 text-base mt-0.5"></i>
                                        <div class="text-[11px]">
                                            <p class="font-bold text-slate-900">Auto email to customer</p>
                                            <p class="text-slate-500">Send invoice by email on each cycle.</p>
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
                                            <p class="font-bold text-slate-900">Vendor Alert</p>
                                            <p class="text-slate-500">Notify you when invoice is sent to customer.</p>
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
                                    This will create a recurring invoice and automatically send it to your customer every month on the 1st.
                                </p>
                            </div>

                            <div class="pt-1">
                                <a href="{{ route('recurring.index') }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-red-600 flex items-center gap-1.5 transition">
                                    <i class="ph ph-calendar-blank text-sm"></i> View Schedule
                                </a>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Turn on recurring billing to automatically generate and dispatch invoices to this client on a recurring schedule.
                        </p>
                    @endif
                </div>

                <!-- 3. Need Help? Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-3">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="ph ph-info text-slate-700 text-base"></i>
                        Need Help?
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        For any questions about invoicing or payment setup, visit our help center or contact support.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('docs.index') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 hover:text-red-600 transition">
                            Visit Help Center &rarr;
                        </a>
                    </div>
                </div>

                <!-- 4. Security & Compliance Footer Badges -->
                <div class="space-y-3.5 pt-2 px-2 text-xs">
                    <div class="flex items-start gap-2.5">
                        <i class="ph ph-currency-circle-dollar text-slate-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <h5 class="font-bold text-slate-900 text-xs">Multi-currency Support</h5>
                            <p class="text-[11px] text-slate-500">Create invoices in your preferred currency.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ph ph-receipt text-slate-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <h5 class="font-bold text-slate-900 text-xs">Automated Tax Calculation</h5>
                            <p class="text-[11px] text-slate-500">Supports country specific tax rules.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ph ph-shield-check text-slate-600 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <h5 class="font-bold text-slate-900 text-xs">Secure & Compliant</h5>
                            <p class="text-[11px] text-slate-500">Your data is protected with enterprise-grade security.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
