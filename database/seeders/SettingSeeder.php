<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'interest_rate',    'value' => '0.30',   'description' => 'Taux d\'intérêt crédit (ex: 0.30 = 30%)'],
            ['key' => 'commodity_rate',   'value' => '1000',   'description' => 'Taux de conversion cacao : 1 kg = X FCFA'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}