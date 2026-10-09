<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::firstOrCreate(
            ['name' => 'Ps Solution Interiors & Architecture'],
            [
                'address' => 'Dhaka, Bangladesh',
                'phone' => null,
                'email' => null,
                'currency' => 'BDT',
                'is_active' => true,
            ]
        );
    }
}
