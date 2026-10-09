<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Custom Services & Products</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage rates, units, and auto-import industry catalogs</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="openPresetModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition border border-indigo-200">
                <i class="ph ph-magic-wand text-base"></i> Import Industry Presets
            </button>
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="ph ph-plus-circle text-base"></i> Add Custom Service
            </button>
        </div>
    </div>

    <!-- Services Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($services->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="ph ph-package text-4xl mb-2 inline-block"></i>
                <p class="text-sm font-medium">Your catalog is currently empty.</p>
                <p class="text-xs text-slate-400 mt-1">Click "Import Industry Presets" above to immediately load ready-made items!</p>
            </div>
        @else
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 font-bold">Service / Product Name</th>
                        <th class="px-4 py-3.5 font-bold">Category</th>
                        <th class="px-4 py-3.5 font-bold">Billing Unit</th>
                        <th class="px-4 py-3.5 font-bold text-right">Standard Rate</th>
                        <th class="px-6 py-3.5 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($services as $s)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $s->name }}</div>
                                @if($s->description)
                                    <div class="text-xs text-slate-500 line-clamp-1">{{ $s->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs font-medium text-slate-600">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">{{ $s->category }}</span>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500 capitalize">
                                {{ $s->unit }}
                            </td>
                            <td class="px-4 py-4 text-right font-bold text-slate-900">
                                ${{ $s->formatted_rate }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="editService('{{ $s->id }}')" class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition" title="Edit">
                                        <i class="ph ph-pencil-simple text-base"></i>
                                    </button>
                                    <button wire:click="deleteService('{{ $s->id }}')" onclick="confirm('Delete this service?') || event.stopImmediatePropagation()" class="p-1.5 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition" title="Delete">
                                        <i class="ph ph-trash text-base"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Preset Importer Modal -->
    @if($showPresetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Predefined Industry Catalogs</h3>
                        <p class="text-xs text-slate-500">Select an industry to automatically import preconfigured services</p>
                    </div>
                    <button wire:click="$set('showPresetModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-96 overflow-y-auto p-1">
                    @foreach($presets as $p)
                        <div class="p-4 rounded-xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition flex flex-col justify-between bg-slate-50/50">
                            <div>
                                <div class="flex items-center gap-2 mb-2 text-blue-600 font-bold text-sm">
                                    <i class="ph ph-{{ $p->icon ?? 'briefcase' }} text-lg"></i>
                                    {{ $p->industry_name }}
                                </div>
                                <p class="text-xs text-slate-600 mb-3">{{ $p->description }}</p>
                                <span class="text-[11px] text-slate-400">{{ $p->items_count }} ready services</span>
                            </div>
                            <div class="pt-4 mt-2 border-t border-slate-100">
                                <button type="button" wire:click="importPreset({{ $p->id }})" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg transition">
                                    1-Click Import Catalog
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 mt-6">
                    <button type="button" wire:click="$set('showPresetModal', false)" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Close</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Create / Edit Custom Service Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <h3 class="font-bold text-slate-900 text-lg">{{ $serviceId ? 'Edit Service' : 'Add New Service' }}</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveService" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Service / Product Title *</label>
                        <input type="text" wire:model.live="name" placeholder="Website Hosting & Maintenance"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                        @error('name') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Category</label>
                            <input type="text" wire:model.live="category" placeholder="Web, Art, SMM..."
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Unit</label>
                            <input type="text" wire:model.live="unit" placeholder="month, hour, project..."
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Standard Rate ($) *</label>
                        <input type="number" step="0.01" wire:model.live="rate" placeholder="150.00"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                        @error('rate') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                        <textarea wire:model.live="description" rows="2" placeholder="Brief scope description..."
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm">Save Service</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
