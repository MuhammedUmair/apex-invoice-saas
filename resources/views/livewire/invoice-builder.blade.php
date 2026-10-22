<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 max-w-5xl mx-auto">
    <!-- Builder Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center p-2 shadow-sm shrink-0">
                <svg class="w-full h-full text-red-600 drop-shadow-sm" viewBox="0 0 900 900" fill="currentColor">
                    <path d="M450 50 L562 278 L732 232 L640 394 L850 472 L670 612 L760 800 L546 728 L490 880 L410 880 L354 728 L140 800 L230 612 L50 472 L260 394 L168 232 L338 278 Z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Red<span class="text-red-600">Maple</span> <span class="font-bold text-slate-700">Invoice Builder</span></h1>
                <p class="text-xs text-slate-500 mt-0.5">Real-time VAT calculation & immutable financial ledger</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                <i class="ph ph-shield-check mr-1.5 text-sm"></i> Concurrency-Safe Sequential Lock
            </span>
        </div>
    </div>

    <form wire:submit.prevent="saveInvoice('issued')" class="mt-6 space-y-8">
        <!-- Metadata Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50/70 p-6 rounded-xl border border-slate-200/60">
            <!-- Client Selection -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Client / Customer <span class="text-rose-500">*</span>
                </label>
                <select wire:model.live="clientId" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">-- Select Client from Directory --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->company_name ? "{$c->company_name} ({$c->name})" : $c->name }}</option>
                    @endforeach
                </select>
                @error('clientId') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Currency -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Currency</label>
                <select wire:model.live="currency" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
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
                <select wire:model.live="countryTaxCode" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
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
                <input type="date" wire:model.live="issueDate" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
            </div>

            <!-- Due Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Payment Due Date</label>
                <input type="date" wire:model.live="dueDate" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
            </div>

            <!-- Custom Tax Rate / Override -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Applied Tax Rate (%)</label>
                    <span class="text-xs text-blue-600 font-semibold">{{ $taxName }}</span>
                </div>
                <input type="number" step="0.01" wire:model.live.debounce.300ms="taxRate" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
            </div>
        </div>

        <!-- Line Items Section -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wide">Line Items & Services</h3>
                <button type="button" wire:click="addItem" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg transition">
                    <i class="ph ph-plus-circle text-base"></i> Add Line Item
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
                                    <input type="text" wire:model.live="items.{{ $idx }}.description" placeholder="Description of service rendered..."
                                           class="w-full px-3 py-1.5 rounded border border-slate-200 text-sm focus:ring-1 focus:ring-blue-500">
                                </td>

                                <!-- Quantity -->
                                <td class="px-3 py-3 align-top pt-4">
                                    <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $idx }}.quantity"
                                           class="w-full px-2 py-1.5 rounded border border-slate-200 text-sm text-center focus:ring-1 focus:ring-blue-500">
                                </td>

                                <!-- Unit -->
                                <td class="px-3 py-3 align-top pt-4">
                                    <input type="text" wire:model.live="items.{{ $idx }}.unit" placeholder="unit"
                                           class="w-full px-2 py-1.5 rounded border border-slate-200 text-sm focus:ring-1 focus:ring-blue-500">
                                </td>

                                <!-- Rate -->
                                <td class="px-3 py-3 align-top pt-4">
                                    <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $idx }}.rate"
                                           class="w-full px-2 py-1.5 rounded border border-slate-200 text-sm text-right focus:ring-1 focus:ring-blue-500">
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

        <!-- Security & Totals Bottom Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
            <!-- Left: Notes, Terms, and PDF Password Security -->
            <div class="space-y-4">
                <!-- PDF Password Protection Option -->
                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/50 space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="isPdfPasswordProtected" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="ph ph-lock-key text-blue-600"></i>
                            Password-Protect Customer PDF
                        </span>
                    </label>
                    <p class="text-xs text-slate-500 pl-6">
                        When enabled, the customer or recipient must enter this password to view or download the generated PDF.
                    </p>

                    @if($isPdfPasswordProtected)
                        <div class="pt-2 pl-6">
                            <input type="password" wire:model.live="pdfPassword" placeholder="Enter secure document password..."
                                   class="w-full px-3 py-2 rounded-lg border border-blue-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <p class="text-[11px] text-blue-700 mt-1">Stored using salted cryptographic one-way hashing.</p>
                        </div>
                    @endif
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Customer Notes / Thank You</label>
                    <textarea wire:model.live="notes" rows="2" placeholder="e.g. Thank you for your business! Please reference invoice number when paying."
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 bg-white"></textarea>
                </div>

                <!-- Terms -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Terms & Payment Instructions</label>
                    <textarea wire:model.live="terms" rows="2"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 bg-white"></textarea>
                </div>
            </div>

            <!-- Right: Financial Ledger Totals -->
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
                            <input type="number" step="0.01" wire:model.live.debounce.300ms="discountAmount" placeholder="0.00"
                                   class="w-full text-right px-2 py-1 rounded border border-slate-300 text-xs focus:ring-1 focus:ring-blue-500 bg-white">
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
                    <button type="button" wire:click="saveInvoice('draft')" class="flex-1 py-2.5 px-4 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-xl transition">
                        Save as Draft
                    </button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                        <i class="ph ph-check-circle text-base"></i>
                        Issue Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
