<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::firstOrCreate(['id' => 1], [
            'nom_entreprise' => 'Mon Entreprise',
            'devise' => 'Ar',
        ]);
    }
}