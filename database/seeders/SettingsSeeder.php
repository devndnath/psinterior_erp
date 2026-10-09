<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'company.name' => [config('app.name'), 'company'],
            'company.address' => ['', 'company'],
            'company.phone' => ['', 'company'],
            'company.email' => ['', 'company'],
            'company.website' => ['', 'company'],

            'finance.currency' => ['BDT', 'finance'],
            'finance.tax_rate' => [0, 'finance'],
            'finance.date_format' => ['d M, Y', 'finance'],

            'prefix.lead' => ['LEAD', 'prefix'],
            'prefix.client' => ['CLIENT', 'prefix'],
            'prefix.project' => ['PRJ', 'prefix'],
            'prefix.quotation' => ['QUO', 'prefix'],
            'prefix.invoice' => ['INV', 'prefix'],
            'prefix.purchase_order' => ['PO', 'prefix'],

            'mail.from_address' => [config('mail.from.address'), 'mail'],
            'mail.from_name' => [config('mail.from.name'), 'mail'],
        ];

        foreach ($defaults as $key => [$value, $group]) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
    }
}
