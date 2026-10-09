<div class="space-y-6">
    <!-- Top Bar with Search & Add Client -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <i class="ph ph-magnifying-glass absolute left-3 top-3 text-slate-400 text-lg"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, company, email, VAT/Tax ID..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 bg-white outline-none shadow-sm">
        </div>
        <div>
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="ph ph-user-plus text-base"></i> Add New Client
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
                        <th class="px-4 py-3.5 font-bold">Tax / VAT ID</th>
                        <th class="px-4 py-3.5 font-bold">Currency & Terms</th>
                        <th class="px-4 py-3.5 font-bold text-right">Outstanding Due</th>
                        <th class="px-6 py-3.5 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($clients as $c)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Company & Name -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $c->company_name ?? $c->name }}</div>
                                @if($c->company_name)
                                    <div class="text-xs text-slate-500">{{ $c->name }}</div>
                                @endif
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $c->country_code }} &bull; {{ $c->city ?? 'N/A' }}</div>
                            </td>

                            <!-- Contact -->
                            <td class="px-4 py-4 text-xs text-slate-600 space-y-0.5">
                                <div class="flex items-center gap-1.5"><i class="ph ph-envelope text-slate-400"></i> {{ $c->email }}</div>
                                @if($c->phone)
                                    <div class="flex items-center gap-1.5"><i class="ph ph-phone text-slate-400"></i> {{ $c->phone }}</div>
                                @endif
                            </td>

                            <!-- Tax ID & Exemption -->
                            <td class="px-4 py-4 text-xs">
                                @if($c->tax_id)
                                    <span class="font-mono text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ $c->tax_id }}</span>
                                @else
                                    <span class="text-slate-400">None</span>
                                @endif
                                @if($c->is_tax_exempt)
                                    <span class="block mt-1 text-[10px] text-emerald-600 font-bold uppercase">Tax Exempt</span>
                                @endif
                            </td>

                            <!-- Currency & Terms -->
                            <td class="px-4 py-4 text-xs text-slate-600">
                                <span class="font-bold text-slate-800">{{ $c->preferred_currency }}</span>
                                <span class="text-slate-400 block uppercase text-[10px]">{{ str_replace('_', ' ', $c->default_payment_terms) }}</span>
                            </td>

                            <!-- Outstanding Due -->
                            <td class="px-4 py-4 text-right">
                                @php $dueCents = $c->outstanding_balance_cents; @endphp
                                @if($dueCents > 0)
                                    <span class="font-bold text-rose-600 text-sm">${{ number_format($dueCents / 100, 2) }}</span>
                                @else
                                    <span class="text-xs text-emerald-600 font-semibold">All Clear ($0.00)</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('invoices.create') }}?client_id={{ $c->id }}" class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="New Invoice">
                                        <i class="ph ph-receipt text-base"></i>
                                    </a>
                                    <button wire:click="deleteClient('{{ $c->id }}')" onclick="confirm('Archive this client?') || event.stopImmediatePropagation()" class="p-1.5 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition" title="Delete">
                                        <i class="ph ph-trash text-base"></i>
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

    <!-- Add Client Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <h3 class="font-bold text-slate-900 text-lg">Add New Customer</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveClient" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Company / Organization</label>
                            <input type="text" wire:model.live="company_name" placeholder="Globex Corp"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Contact Person Name *</label>
                            <input type="text" wire:model.live="name" placeholder="Sarah Jenkins"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                            @error('name') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address *</label>
                            <input type="email" wire:model.live="email" placeholder="billing@globex.com"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                            @error('email') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                            <input type="text" wire:model.live="phone" placeholder="+1 (555) 019-2834"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tax / VAT ID</label>
                            <input type="text" wire:model.live="tax_id" placeholder="GB987654321"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Country</label>
                            <select wire:model.live="country_code" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="US">United States</option>
                                <option value="GB">United Kingdom</option>
                                <option value="DE">Germany</option>
                                <option value="FR">France</option>
                                <option value="AE">UAE</option>
                                <option value="SA">Saudi Arabia</option>
                                <option value="AU">Australia</option>
                                <option value="CA">Canada</option>
                                <option value="IN">India</option>
                                <option value="TR">Turkey</option>
                                <option value="PK">Pakistan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Currency</label>
                            <select wire:model.live="preferred_currency" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="AED">AED (د.إ)</option>
                                <option value="SAR">SAR (﷼)</option>
                                <option value="CAD">CAD (C$)</option>
                                <option value="AUD">AUD (A$)</option>
                                <option value="INR">INR (₹)</option>
                                <option value="TRY">TRY (₺)</option>
                                <option value="PKR">PKR (Rs)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Billing Address</label>
                        <textarea wire:model.live="address" rows="2" placeholder="Suite 400, Canary Wharf, London"
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" wire:model.live="is_tax_exempt" id="tax_exempt_check" class="w-4 h-4 text-blue-600 rounded border-slate-300">
                        <label for="tax_exempt_check" class="text-xs text-slate-700 font-semibold cursor-pointer">Client is Tax / VAT Exempt</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm">Save Client</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
