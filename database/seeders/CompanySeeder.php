<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->updateOrCreate(
            ['name' => 'LUMIÈRE DINING'],
            [
                'default_tax_rate' => Company::DEFAULT_TAX_RATE,
                'default_service_charge_rate' => Company::DEFAULT_SERVICE_CHARGE_RATE,
                'uses_qr_code' => true,
            ],
        );
    }
}
