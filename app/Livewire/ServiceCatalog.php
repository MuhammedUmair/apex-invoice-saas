<?php

namespace App\Livewire;

use App\Models\IndustryPreset;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ServiceCatalog extends Component
{
    public bool $showCreateModal = false;
    public bool $showPresetModal = false;

    // Form fields
    public ?string $serviceId = null;
    public string $name = '';
    public string $category = 'General';
    public string $description = '';
    public string $unit = 'unit';
    public float $rate = 0.00;
    public float $default_tax_rate = 0.00;
    public bool $is_active = true;

    // Preset import selection
    public ?int $selectedPresetId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'category' => 'required|string|max:100',
        'description' => 'nullable|string',
        'unit' => 'required|string|max:50',
        'rate' => 'required|numeric|min:0',
        'default_tax_rate' => 'numeric|min:0',
    ];

    public function openCreateModal()
    {
        $this->reset(['serviceId', 'name', 'category', 'description']);
        $this->unit = 'unit';
        $this->rate = 0.00;
        $this->default_tax_rate = 0.00;
        $this->is_active = true;
        $this->showCreateModal = true;
    }

    public function editService(string $id)
    {
        $service = Service::findOrFail($id);
        $this->serviceId = $service->id;
        $this->name = $service->name;
        $this->category = $service->category;
        $this->description = $service->description ?? '';
        $this->unit = $service->unit;
        $this->rate = $service->rate_cents / 100;
        $this->default_tax_rate = (float) $service->default_tax_rate;
        $this->is_active = $service->is_active;
        $this->showCreateModal = true;
    }

    public function saveService()
    {
        $this->validate();
        $rateCents = (int) round($this->rate * 100);

        if ($this->serviceId) {
            $service = Service::findOrFail($this->serviceId);
            $service->update([
                'name' => $this->name,
                'category' => $this->category,
                'description' => $this->description,
                'unit' => $this->unit,
                'rate_cents' => $rateCents,
                'default_tax_rate' => $this->default_tax_rate,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Service updated successfully.');
        } else {
            Service::create([
                'workspace_id' => Auth::user()->workspace_id,
                'name' => $this->name,
                'category' => $this->category,
                'description' => $this->description,
                'unit' => $this->unit,
                'rate_cents' => $rateCents,
                'default_tax_rate' => $this->default_tax_rate,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Service added to your catalog.');
        }

        $this->showCreateModal = false;
    }

    public function deleteService(string $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        session()->flash('success', 'Service removed from active catalog.');
    }

    public function openPresetModal()
    {
        $this->showPresetModal = true;
    }

    public function importPreset(int $presetId)
    {
        $preset = IndustryPreset::with('items')->findOrFail($presetId);
        $workspaceId = Auth::user()->workspace_id;
        $count = 0;

        foreach ($preset->items as $item) {
            Service::firstOrCreate(
                [
                    'workspace_id' => $workspaceId,
                    'name' => $item->name,
                ],
                [
                    'category' => $item->category,
                    'description' => $item->description,
                    'unit' => $item->default_unit,
                    'rate_cents' => $item->suggested_rate_cents,
                    'default_tax_rate' => 0.00,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $this->showPresetModal = false;
        session()->flash('success', "Imported {$count} services from '{$preset->industry_name}' into your catalog!");
    }

    public function render()
    {
        $services = Service::orderBy('category')->orderBy('name')->get();
        $presets = IndustryPreset::withCount('items')->get();

        return view('livewire.service-catalog', [
            'services' => $services,
            'presets' => $presets,
        ]);
    }
}
