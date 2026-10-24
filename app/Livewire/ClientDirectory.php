<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class ClientDirectory extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?string $editingClientId = null;

    // Form fields
    public string $name = '';
    public string $company_name = '';
    public string $email = '';
    public string $phone = '';
    public string $tax_id = '';
    public string $address = '';
    public string $city = '';
    public string $country_code = 'US';
    public string $preferred_currency = 'USD';
    public string $preferred_language = 'en';
    public string $default_payment_terms = 'net_14';
    public bool $is_tax_exempt = false;

    public static array $supportedLanguages = [
        'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇺🇸'],
        'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'flag' => '🇵🇰'],
        'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'flag' => '🇮🇳'],
        'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'flag' => '🇹🇷'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'flag' => '🇦🇪'],
        'es' => ['name' => 'Spanish', 'native' => 'Español', 'flag' => '🇪🇸'],
        'fr' => ['name' => 'French', 'native' => 'Français', 'flag' => '🇫🇷'],
        'de' => ['name' => 'German', 'native' => 'Deutsch', 'flag' => '🇩🇪'],
        'zh' => ['name' => 'Chinese', 'native' => '中文', 'flag' => '🇨🇳'],
    ];

    public static array $paymentTermsOptions = [
        'due_on_receipt' => 'Due on Receipt',
        'net_7' => 'Net 7 Days',
        'net_14' => 'Net 14 Days',
        'net_30' => 'Net 30 Days',
        'net_60' => 'Net 60 Days',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'company_name' => 'nullable|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:50',
        'tax_id' => 'nullable|string|max:100',
        'address' => 'nullable|string',
        'city' => 'nullable|string|max:100',
        'country_code' => 'required|string|size:2',
        'preferred_currency' => 'required|string|size:3',
        'preferred_language' => 'required|string|max:10',
        'default_payment_terms' => 'required|string|max:30',
        'is_tax_exempt' => 'boolean',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'company_name', 'email', 'phone', 'tax_id', 'address', 'city', 'editingClientId']);
        $this->is_tax_exempt = false;
        $this->country_code = 'US';
        $this->preferred_currency = 'USD';
        $this->preferred_language = 'en';
        $this->default_payment_terms = 'net_14';
        $this->showEditModal = false;
        $this->showCreateModal = true;
    }

    public function editClient(string $id)
    {
        $client = Client::findOrFail($id);
        $this->editingClientId = $client->id;
        $this->name = $client->name;
        $this->company_name = $client->company_name ?? '';
        $this->email = $client->email;
        $this->phone = $client->phone ?? '';
        $this->tax_id = $client->tax_id ?? '';
        $this->address = $client->address ?? '';
        $this->city = $client->city ?? '';
        $this->country_code = $client->country_code ?? 'US';
        $this->preferred_currency = $client->preferred_currency ?? 'USD';
        $this->preferred_language = $client->preferred_language ?? 'en';
        $this->default_payment_terms = $client->default_payment_terms ?? 'net_14';
        $this->is_tax_exempt = (bool) $client->is_tax_exempt;

        $this->showCreateModal = false;
        $this->showEditModal = true;
    }

    public function updateClient()
    {
        $this->validate();

        $client = Client::findOrFail($this->editingClientId);
        $client->update([
            'name' => $this->name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'city' => $this->city,
            'country_code' => $this->country_code,
            'preferred_currency' => $this->preferred_currency,
            'preferred_language' => $this->preferred_language,
            'default_payment_terms' => $this->default_payment_terms,
            'is_tax_exempt' => $this->is_tax_exempt,
        ]);

        $this->showEditModal = false;
        $this->editingClientId = null;
        session()->flash('success', "Client {$this->name} updated successfully.");
    }

    public function saveClient()
    {
        $this->validate();

        Client::create([
            'name' => $this->name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'city' => $this->city,
            'country_code' => $this->country_code,
            'preferred_currency' => $this->preferred_currency,
            'preferred_language' => $this->preferred_language,
            'default_payment_terms' => $this->default_payment_terms,
            'is_tax_exempt' => $this->is_tax_exempt,
        ]);

        $this->showCreateModal = false;
        session()->flash('success', "Client {$this->name} added successfully.");
    }

    public function deleteClient(string $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        session()->flash('success', 'Client archived.');
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('company_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('tax_id', 'like', "%{$this->search}%")
                        ->orWhere('preferred_language', 'like', "%{$this->search}%");
                });
            })
            ->withCount('invoices')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('livewire.client-directory', [
            'clients' => $clients,
            'languages' => self::$supportedLanguages,
            'termsOptions' => self::$paymentTermsOptions,
        ]);
    }
}
