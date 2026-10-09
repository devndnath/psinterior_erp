<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tabbed settings page (spec §56). Every PDF template and every numbering
 * prefix reads from here at render time — nothing in the application is
 * allowed to hard-code a company name, tax rate or prefix
 * (ARCHITECTURE.md §1 assumption, spec §55: "must come from settings, not
 * hard-coded values").
 */
#[Layout('layouts.admin', ['title' => 'Settings'])]
#[Title('Settings')]
class Index extends Component
{
    use WithFileUploads;

    public string $tab = 'company';

    // Company
    public string $company_name = '';
    public string $company_address = '';
    public string $company_phone = '';
    public string $company_email = '';
    public string $company_website = '';
    public $logo;

    // Finance / Tax
    public string $currency = 'BDT';
    public string $tax_rate = '0';
    public string $date_format = 'd M, Y';

    // Prefixes
    public string $prefix_lead = 'LEAD';
    public string $prefix_client = 'CLIENT';
    public string $prefix_project = 'PRJ';
    public string $prefix_quotation = 'QUO';
    public string $prefix_invoice = 'INV';
    public string $prefix_purchase_order = 'PO';

    // Email
    public string $mail_from_address = '';
    public string $mail_from_name = '';

    public function mount(): void
    {
        $this->company_name = Setting::get('company.name', config('app.name'));
        $this->company_address = Setting::get('company.address', '');
        $this->company_phone = Setting::get('company.phone', '');
        $this->company_email = Setting::get('company.email', '');
        $this->company_website = Setting::get('company.website', '');

        $this->currency = Setting::get('finance.currency', 'BDT');
        $this->tax_rate = (string) Setting::get('finance.tax_rate', '0');
        $this->date_format = Setting::get('finance.date_format', 'd M, Y');

        $this->prefix_lead = Setting::get('prefix.lead', 'LEAD');
        $this->prefix_client = Setting::get('prefix.client', 'CLIENT');
        $this->prefix_project = Setting::get('prefix.project', 'PRJ');
        $this->prefix_quotation = Setting::get('prefix.quotation', 'QUO');
        $this->prefix_invoice = Setting::get('prefix.invoice', 'INV');
        $this->prefix_purchase_order = Setting::get('prefix.purchase_order', 'PO');

        $this->mail_from_address = Setting::get('mail.from_address', '');
        $this->mail_from_name = Setting::get('mail.from_name', config('app.name'));
    }

    public function saveCompany(): void
    {
        $this->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->logo) {
            $path = $this->logo->store('company', 'public');
            Setting::set('company.logo_path', $path, 'company');
        }

        Setting::set('company.name', $this->company_name, 'company');
        Setting::set('company.address', $this->company_address, 'company');
        Setting::set('company.phone', $this->company_phone, 'company');
        Setting::set('company.email', $this->company_email, 'company');
        Setting::set('company.website', $this->company_website, 'company');

        $this->dispatch('toast', message: 'Settings saved.', type: 'success');
    }

    public function saveFinance(): void
    {
        $this->validate([
            'currency' => ['required', 'string', 'size:3'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'date_format' => ['required', 'string', 'max:50'],
        ]);

        Setting::set('finance.currency', strtoupper($this->currency), 'finance');
        Setting::set('finance.tax_rate', (float) $this->tax_rate, 'finance');
        Setting::set('finance.date_format', $this->date_format, 'finance');

        $this->dispatch('toast', message: 'Settings saved.', type: 'success');
    }

    public function savePrefixes(): void
    {
        $this->validate([
            'prefix_lead' => ['required', 'string', 'max:10', 'alpha'],
            'prefix_client' => ['required', 'string', 'max:10', 'alpha'],
            'prefix_project' => ['required', 'string', 'max:10', 'alpha'],
            'prefix_quotation' => ['required', 'string', 'max:10', 'alpha'],
            'prefix_invoice' => ['required', 'string', 'max:10', 'alpha'],
            'prefix_purchase_order' => ['required', 'string', 'max:10', 'alpha'],
        ]);

        $map = [
            'LEAD' => strtoupper($this->prefix_lead),
            'CLIENT' => strtoupper($this->prefix_client),
            'PRJ' => strtoupper($this->prefix_project),
            'QUO' => strtoupper($this->prefix_quotation),
            'INV' => strtoupper($this->prefix_invoice),
            'PO' => strtoupper($this->prefix_purchase_order),
        ];

        $numbering = app(\App\Services\NumberingService::class);

        foreach ($map as $key => $prefix) {
            // Changes what NEW numbers look like without renumbering
            // anything already issued (NumberingService::updatePrefix
            // only ever touches `prefix`, never `last_number`).
            $numbering->updatePrefix($key, $prefix);
        }

        Setting::set('prefix.lead', $map['LEAD'], 'prefix');
        Setting::set('prefix.client', $map['CLIENT'], 'prefix');
        Setting::set('prefix.project', $map['PRJ'], 'prefix');
        Setting::set('prefix.quotation', $map['QUO'], 'prefix');
        Setting::set('prefix.invoice', $map['INV'], 'prefix');
        Setting::set('prefix.purchase_order', $map['PO'], 'prefix');

        $this->dispatch('toast', message: 'Settings saved.', type: 'success');
    }

    public function saveEmail(): void
    {
        $this->validate([
            'mail_from_address' => ['required', 'email'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);

        Setting::set('mail.from_address', $this->mail_from_address, 'mail');
        Setting::set('mail.from_name', $this->mail_from_name, 'mail');

        $this->dispatch('toast', message: 'Settings saved.', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
