<div class="space-y-6">
    <!-- Top Bar with Search & Add Client -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <i class="ph ph-magnifying-glass absolute left-3 top-3 text-slate-400 text-lg"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, company, email, language (Urdu, Hindi, Turkish)..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-red-500 bg-white outline-none shadow-sm">
        </div>
        <div>
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="ph ph-user-plus text-base"></i> {{ __('Add New Client') }}
            </button>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($clients->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="ph ph-users text-4xl mb-2 inline-block"></i>
                <p class="text-sm font-medium">No clients found matching your search.</p>
            </div>
        @else
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 font-bold">Client / Company</th>
                        <th class="px-4 py-3.5 font-bold">Contact Info</th>
                        <th class="px-4 py-3.5 font-bold">Language & Currency</th>
                        <th class="px-4 py-3.5 font-bold">Tax & Terms</th>
                        <th class="px-4 py-3.5 font-bold text-center">Invoices</th>
                        <th class="px-6 py-3.5 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($clients as $c)
                        @php
                            $langMeta = $languages[$c->preferred_language] ?? ['name' => strtoupper($c->preferred_language), 'native' => '', 'flag' => '🌐'];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Company & Name -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $c->company_name ?: $c->name }}</div>
                                @if($c->company_name)
                                    <div class="text-xs text-slate-500">{{ $c->name }}</div>
                                @endif
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium text-[10px]">{{ $c->country_code }}</span>
                                    <span>{{ $c->city ?: 'Location not set' }}</span>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="px-4 py-4 text-xs text-slate-600 space-y-0.5">
                                <div class="flex items-center gap-1.5"><i class="ph ph-envelope text-slate-400"></i> {{ $c->email }}</div>
                                @if($c->phone)
                                    <div class="flex items-center gap-1.5"><i class="ph ph-phone text-slate-400"></i> {{ $c->phone }}</div>
                                @endif
                            </td>

                            <!-- Language & Currency -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        <span>{{ $langMeta['flag'] }}</span>
                                        <span>{{ $langMeta['name'] }}</span>
                                        @if(!empty($langMeta['native']) && $langMeta['native'] !== $langMeta['name'])
                                            <span class="text-slate-400 text-[10px]">({{ $langMeta['native'] }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="text-xs font-medium text-slate-600 mt-1">
                                    Billing: <span class="font-bold text-slate-800">{{ $c->preferred_currency }}</span>
                                </div>
                            </td>

                            <!-- Tax & Terms -->
                            <td class="px-4 py-4 text-xs">
                                <div class="font-mono text-slate-700">{{ $c->tax_id ?: 'No Tax ID' }}</div>
                                <div class="text-slate-500 capitalize mt-0.5">
                                    {{ str_replace('_', ' ', $c->default_payment_terms) }}
                                </div>
                                @if($c->is_tax_exempt)
                                    <span class="inline-block mt-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold">Tax Exempt</span>
                                @endif
                            </td>

                            <!-- Invoices Count -->
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $c->invoices_count }} invoices
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="editClient('{{ $c->id }}')"
                                            class="text-slate-400 hover:text-blue-600 transition p-1.5 rounded-lg hover:bg-slate-100" title="{{ __('Edit Client') }}">
                                        <i class="ph ph-pencil-simple text-lg"></i>
                                    </button>
                                    <button wire:click="deleteClient('{{ $c->id }}')" wire:confirm="Archive this client? All client invoices remain preserved."
                                            class="text-slate-400 hover:text-rose-600 transition p-1.5 rounded-lg hover:bg-slate-100" title="Archive Client">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4 border-t border-slate-100">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

    <!-- Add/Edit Client Modal -->
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">{{ $showEditModal ? __('Edit Client') : __('Add New Client') }}</h3>
                        <p class="text-xs text-slate-500">Multilingual invoice and regional currency preferences</p>
                    </div>
                    <button wire:click="$set('{{ $showEditModal ? 'showEditModal' : 'showCreateModal' }}', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $showEditModal ? 'updateClient' : 'saveClient' }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Company / Organization</label>
                            <input type="text" wire:model.live="company_name" placeholder="Globex Corp"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Contact Person Name *</label>
                            <input type="text" wire:model.live="name" placeholder="Sarah Jenkins"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                            @error('name') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address *</label>
                            <input type="email" wire:model.live="email" placeholder="billing@globex.com"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                            @error('email') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                            <input type="text" wire:model.live="phone" placeholder="+1 (555) 019-2834"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>

                    <!-- Preferred Language & Currency -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Client Invoice Language *
                            </label>
                            <select wire:model.live="preferred_language" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="en">🇺🇸 English (Default)</option>
                                <option value="ur">🇵🇰 Urdu (اردو) - RTL</option>
                                <option value="hi">🇮🇳 Hindi (हिन्दी)</option>
                                <option value="tr">🇹🇷 Turkish (Türkçe)</option>
                                <option value="ar">🇦🇪 Arabic (العربية) - RTL</option>
                                <option value="es">🇪🇸 Spanish (Español)</option>
                                <option value="fr">🇫🇷 French (Français)</option>
                                <option value="de">🇩🇪 German (Deutsch)</option>
                                <option value="zh">🇨🇳 Chinese (中文)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">Invoices & customer portal render in this language</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Preferred Currency *
                            </label>
                            <select wire:model.live="preferred_currency" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="USD">USD ($ - United States Dollar)</option>
                                <option value="EUR">EUR (€ - Euro)</option>
                                <option value="GBP">GBP (£ - British Pound)</option>
                                <option value="AED">AED (د.إ - UAE Dirham)</option>
                                <option value="SAR">SAR (﷼ - Saudi Riyal)</option>
                                <option value="CAD">CAD (C$ - Canadian Dollar)</option>
                                <option value="AUD">AUD (A$ - Australian Dollar)</option>
                                <option value="INR">INR (₹ - Indian Rupee)</option>
                                <option value="TRY">TRY (₺ - Turkish Lira)</option>
                                <option value="PKR">PKR (Rs - Pakistani Rupee)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">Used for automatic line items & payment gateways</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tax / VAT ID</label>
                            <input type="text" wire:model.live="tax_id" placeholder="GB987654321 / TR123..."
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Country</label>
                            <select wire:model.live="country_code" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="US">United States</option>
                                <option value="PK">Pakistan</option>
                                <option value="IN">India</option>
                                <option value="TR">Turkey</option>
                                <option value="AE">UAE</option>
                                <option value="SA">Saudi Arabia</option>
                                <option value="GB">United Kingdom</option>
                                <option value="CA">Canada</option>
                                <option value="AU">Australia</option>
                                <option value="DE">Germany</option>
                                <option value="FR">France</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Payment Terms</label>
                            <select wire:model.live="default_payment_terms" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="net_14">Net 14 Days</option>
                                <option value="due_on_receipt">Due on Receipt</option>
                                <option value="net_7">Net 7 Days</option>
                                <option value="net_30">Net 30 Days</option>
                                <option value="net_60">Net 60 Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">City</label>
                            <input type="text" wire:model.live="city" placeholder="London / Istanbul / Karachi / Dubai"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="is_tax_exempt" id="tax_exempt_check" class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                <span class="text-xs font-medium text-slate-700">Client is Tax / VAT Exempt</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Full Billing Address</label>
                        <textarea wire:model.live="address" rows="2" placeholder="Suite 400, Financial District..."
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="$set('{{ $showEditModal ? 'showEditModal' : 'showCreateModal' }}', false)"
                                class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-sm font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-sm transition">
                            {{ $showEditModal ? 'Save Changes' : 'Save Client' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
