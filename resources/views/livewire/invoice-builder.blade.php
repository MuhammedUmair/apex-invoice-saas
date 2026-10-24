<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 max-w-5xl mx-auto">
    <!-- Builder Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center p-2 shadow-sm shrink-0">
                <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">
                    Invoicy <span class="text-red-600 font-extrabold">by RedMaple</span>
                    @if($isEditing)
                        <span class="text-xs bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full font-bold ml-2">Revising #{{ $invoiceNumber }}</span>
                    @else
                        <span class="font-bold text-slate-500 text-base">Invoice Studio</span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Real-time multi-currency VAT calculation, client CRM integration & automated ledger</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <i class="ph ph-shield-check mr-1.5 text-sm"></i> Concurrency-Safe Ledger Lock
            </span>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i class="ph ph-check-circle text-base text-emerald-600"></i>
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="saveInvoice('issued')" class="mt-6 space-y-8">
        <!-- Metadata Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50/70 p-6 rounded-xl border border-slate-200/60">
            <!-- Client Selection / Walk-in Toggle -->
            <div class="md:col-span-1">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Client / Customer <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center bg-slate-200 p-0.5 rounded-lg text-[11px] font-semibold">
                        <button type="button" wire:click="disableWalkin" 
                                class="px-2 py-0.5 rounded transition {{ !$isWalkin ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                            CRM
                        </button>
                        <button type="button" wire:click="enableWalkin" 
                                class="px-2 py-0.5 rounded transition {{ $isWalkin ? 'bg-red-600 text-white font-bold shadow' : 'text-slate-600 hover:text-slate-900' }}">
                            Walk-in
                        </button>
                    </div>
                </div>

                @if(!$isWalkin)
                    <select wire:model.live="clientId" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                        <option value="">-- Select Client from Directory --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name ? "{$c->company_name} ({$c->name})" : $c->name }}</option>
                        @endforeach
                    </select>
                    @error('clientId') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                @else
                    <div class="space-y-2 bg-white p-3 rounded-lg border border-red-200 shadow-sm">
                        <input type="text" wire:model.live="walkinName" placeholder="Customer Name (e.g. John Doe / Walk-in)" 
                               class="w-full px-2.5 py-1.5 rounded border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        @error('walkinName') <span class="text-rose-600 text-xs block">{{ $message }}</span> @enderror
                        
                        <div class="grid grid-cols-2 gap-2">
                            <input type="email" wire:model.live="walkinEmail" placeholder="Email (optional)" 
                                   class="w-full px-2 py-1.5 rounded border border-slate-300 text-xs">
                            <input type="text" wire:model.live="walkinPhone" placeholder="Phone (optional)" 
                                   class="w-full px-2 py-1.5 rounded border border-slate-300 text-xs">
                        </div>
                    </div>
                @endif
            </div>

            <!-- Currency -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Currency</label>
                <select wire:model.live="currency" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                    <option value="USD">USD ($) - US Dollar</option>
                    <option value="EUR">EUR (€) - Euro</option>
                    <option value="GBP">GBP (£) - British Pound</option>
                    <option value="AED">AED (د.إ) - UAE Dirham</option>
                    <option value="SAR">SAR (﷼) - Saudi Riyal</option>
                    <option value="CAD">CAD (C$) - Canadian Dollar</option>
                    <option value="AUD">AUD (A$) - Australian Dollar</option>
                    <option value="INR">INR (₹) - Indian Rupee</option>
                    <option value="TRY">TRY (₺) - Turkish Lira</option>
                    <option value="PKR">PKR (Rs) - Pakistani Rupee</option>
                </select>
            </div>

            <!-- Country Tax Preset -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Country Tax Rule</label>
                <select wire:model.live="countryTaxCode" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                    <option value="US">United States - 0% (Sales Tax)</option>
                    <option value="GB">United Kingdom - 20% VAT</option>
                    <option value="DE">Germany - 19% MwSt</option>
                    <option value="FR">France - 20% TVA</option>
                    <option value="ES">Spain - 21% IVA</option>
                    <option value="AE">UAE - 5% VAT</option>
                    <option value="SA">Saudi Arabia - 15% ZATCA VAT</option>
                    <option value="AU">Australia - 10% GST</option>
                    <option value="CA">Canada - 13% GST/HST</option>
                    <option value="IN">India - 18% GST</option>
                    <option value="TR">Turkey - 20% KDV</option>
                    <option value="PK">Pakistan - 16% SST</option>
                </select>
            </div>

            <!-- Issue Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Issue Date</label>
                <input type="date" wire:model.live="issueDate" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
            </div>

            <!-- Due Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Payment Due Date</label>
                <input type="date" wire:model.live="dueDate" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
            </div>

            <!-- Custom Tax Rate / Override -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Applied Tax Rate (%)</label>
                    <span class="text-xs text-red-600 font-bold">{{ $taxName }}</span>
                </div>
                <input type="number" step="0.01" wire:model.live.debounce.250ms="taxRate" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
            </div>
        </div>

        <!-- Generic Presets Ribbon (Fast 1-Click Invoicing) -->
        <div class="p-3 bg-red-50/50 rounded-xl border border-red-100 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-red-800 flex items-center gap-1 shrink-0">
                <i class="ph ph-lightning text-sm text-red-600"></i> Fast Presets:
            </span>
            <button type="button" wire:click="addPresetItem('consulting')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-2.5 py-1 rounded-lg border border-red-200 transition shadow-xs">
                + Consulting ($100/hr)
            </button>
            <button type="button" wire:click="addPresetItem('web_dev')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-2.5 py-1 rounded-lg border border-red-200 transition shadow-xs">
                + Web Design ($750)
            </button>
            <button type="button" wire:click="addPresetItem('retainer')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-2.5 py-1 rounded-lg border border-red-200 transition shadow-xs">
                + Monthly Retainer ($500)
            </button>
            <button type="button" wire:click="addPresetItem('labor')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-2.5 py-1 rounded-lg border border-red-200 transition shadow-xs">
                + Technical Labor ($75/hr)
            </button>
            <button type="button" wire:click="addPresetItem('marketing')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-2.5 py-1 rounded-lg border border-red-200 transition shadow-xs">
                + Marketing ($250)
            </button>
            <button type="button" wire:click="addPresetItem('product')" class="text-xs bg-white hover:bg-red-600 hover:text-white text-slate-700 font-semibold px-2.5 py-1 rounded-lg border border-red-200 transition shadow-xs">
                + Merchandise ($45)
            </button>
        </div>

        <!-- Line Items Section -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wide">Line Items & Services</h3>
                <button type="button" wire:click="addItem" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-lg transition border border-red-200">
                    <i class="ph ph-plus-circle text-base"></i> Add Custom Row
                </button>
            </div>

            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100/80 text-slate-600 text-[11px] uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 w-1/3">Item / Service Autofill</th>
                            <th class="px-3 py-3 w-24">Qty</th>
                            <th class="px-3 py-3 w-28">Unit</th>
                            <th class="px-3 py-3 w-32">Rate ({{ $currency }})</th>
                            <th class="px-4 py-3 w-32 text-right">Amount</th>
                            <th class="px-3 py-3 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $idx => $item)
                            <tr class="hover:bg-slate-50/50 transition">
                                <!-- Item autofill & Description -->
                                <td class="px-4 py-3 space-y-1.5">
                                    <select wire:change="selectService({{ $idx }}, $event.target.value)" class="w-full text-xs px-2 py-1 rounded border border-slate-200 bg-slate-50 text-slate-700 mb-1">
                                        <option value="">-- Choose from Catalog or Type Below --</option>
                                        @foreach($services as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }} (${{ $s->formatted_rate }})</option>
                                        @endforeach
                                    </select>
                                    <input type="text" wire:model.live.debounce.250ms="items.{{ $idx }}.description" placeholder="Description of service rendered..."
                                           class="w-full px-3 py-1.5 rounded border border-slate-200 text-sm focus:ring-1 focus:ring-red-500">
                                </td>

                                <!-- Quantity -->
                                <td class="px-3 py-3 align-top pt-4">
                                    <input type="number" step="0.01" wire:model.live.debounce.250ms="items.{{ $idx }}.quantity"
                                           class="w-full px-2 py-1.5 rounded border border-slate-200 text-sm text-center focus:ring-1 focus:ring-red-500">
                                </td>

                                <!-- Unit -->
                                <td class="px-3 py-3 align-top pt-4">
                                    <input type="text" wire:model.live.debounce.250ms="items.{{ $idx }}.unit" placeholder="unit"
                                           class="w-full px-2 py-1.5 rounded border border-slate-200 text-sm focus:ring-1 focus:ring-red-500">
                                </td>

                                <!-- Rate -->
                                <td class="px-3 py-3 align-top pt-4">
                                    <input type="number" step="0.01" wire:model.live.debounce.250ms="items.{{ $idx }}.rate"
                                           class="w-full px-2 py-1.5 rounded border border-slate-200 text-sm text-right focus:ring-1 focus:ring-red-500">
                                </td>

                                <!-- Amount (computed) -->
                                <td class="px-4 py-3 align-top pt-5 text-right font-bold text-slate-800">
                                    {{ number_format($item['amount'], 2) }}
                                </td>

                                <!-- Remove -->
                                <td class="px-3 py-3 align-top pt-4 text-center">
                                    <button type="button" wire:click="removeItem({{ $idx }})" class="text-slate-300 hover:text-rose-500 transition p-1" title="Remove row">
                                        <i class="ph ph-trash text-base"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Planning & Automated Dispatch Settings -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/70 p-6 rounded-xl border border-slate-200">
            <!-- Option 13: Direct Email on Publish -->
            <div class="space-y-3">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" wire:model.live="sendEmailToClient" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 mt-0.5">
                    <div>
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="ph ph-envelope-simple text-red-600"></i>
                            Email Invoice to Customer on Publish
                        </span>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Automatically dispatches the invoice with direct portal & PDF download link to the client's registered email address in CRM.
                        </p>
                    </div>
                </label>

                <!-- PDF Password Protection -->
                <label class="flex items-start gap-2.5 cursor-pointer pt-2 border-t border-slate-200">
                    <input type="checkbox" wire:model.live="isPdfPasswordProtected" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 mt-0.5">
                    <div>
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="ph ph-lock-key text-slate-700"></i>
                            Password-Protect Customer PDF
                        </span>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Recipient must enter a password to open or view the downloaded PDF file.
                        </p>
                    </div>
                </label>

                @if($isPdfPasswordProtected)
                    <div class="pt-2 pl-6">
                        <input type="password" wire:model.live="pdfPassword" placeholder="Enter secure document password..."
                               class="w-full px-3 py-2 rounded-lg border border-red-300 text-sm bg-white focus:ring-2 focus:ring-red-500 outline-none">
                    </div>
                @endif
            </div>

            <!-- Option 14: Future Scheduled Invoice Planning -->
            <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/50 space-y-3">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" wire:model.live="isScheduled" class="w-4 h-4 text-amber-600 rounded border-slate-300 focus:ring-amber-500 mt-0.5">
                    <div>
                        <span class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <i class="ph ph-calendar-check text-amber-600 text-base"></i>
                            Plan Future Invoice (Automated Scheduler)
                        </span>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Draft this invoice today to automatically publish and dispatch to customer on a planned future date.
                        </p>
                    </div>
                </label>

                @if($isScheduled)
                    <div class="space-y-3 pt-2 border-t border-amber-200/80">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Scheduled Dispatch Date</label>
                            <input type="date" wire:model.live="scheduledPublishDate" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-amber-300 text-xs bg-white focus:ring-2 focus:ring-amber-500">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 font-medium">
                            <input type="checkbox" wire:model.live="scheduledSendEmail" class="w-3.5 h-3.5 text-amber-600 rounded">
                            Automatically send to customer email on that date
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 font-medium">
                            <input type="checkbox" wire:model.live="scheduledAlertVendor" class="w-3.5 h-3.5 text-amber-600 rounded">
                            Send alert email to vendor/business owner when sent
                        </label>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes, Terms & Financial Totals Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-2">
            <!-- Left: Notes & Terms -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Customer Notes / Reference</label>
                    <textarea wire:model.live.debounce.300ms="notes" rows="2" placeholder="e.g. Thank you for your business! Please reference invoice number when paying."
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Terms & Payment Instructions</label>
                    <textarea wire:model.live.debounce.300ms="terms" rows="2"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white"></textarea>
                </div>
            </div>

            <!-- Right: Financial Ledger Summary & Action Buttons -->
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-3 flex flex-col justify-between">
                <div class="space-y-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-600 border-b border-slate-200 pb-2">Financial Summary</h4>
                    
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Subtotal:</span>
                        <span class="font-bold text-slate-900">{{ number_format($subtotal, 2) }} {{ $currency }}</span>
                    </div>

                    <!-- Discount Deducted Before Tax (GAAP Rule) -->
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Pre-Tax Discount:</span>
                        <div class="w-32">
                            <input type="number" step="0.01" wire:model.live.debounce.250ms="discountAmount" placeholder="0.00"
                                   class="w-full text-right px-2 py-1 rounded border border-slate-300 text-xs focus:ring-1 focus:ring-red-500 bg-white">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">{{ $taxName }} ({{ number_format($taxRate, 2) }}%):</span>
                        <span class="font-bold text-slate-900">+{{ number_format($taxAmount, 2) }} {{ $currency }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-base font-black text-slate-900">Total Due:</span>
                        <span class="text-2xl font-black text-red-600">{{ number_format($totalAmount, 2) }} {{ $currency }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex items-center gap-3">
                    @if($isEditing)
                        <button type="button" wire:click="saveInvoice('draft')" class="flex-1 py-2.5 px-4 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-xl transition">
                            Save Changes as Draft
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                            <i class="ph ph-check-circle text-base"></i>
                            Save & Revise Invoice
                        </button>
                    @elseif($isScheduled)
                        <button type="submit" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                            <i class="ph ph-clock text-base"></i>
                            Schedule Future Invoice
                        </button>
                    @else
                        <button type="button" wire:click="saveInvoice('draft')" class="flex-1 py-2.5 px-4 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-xl transition">
                            Save as Draft
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                            <i class="ph ph-paper-plane-tilt text-base"></i>
                            Issue & Publish Invoice
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
