<?php

namespace Database\Seeders;

use App\Models\Farmer;
use Illuminate\Database\Seeder;

class FarmerSeeder extends Seeder
{
    public function run(): void
    {
        $farmers = [
            ['identifier' => 'AGR-001', 'firstname' => 'Kouassi',  'lastname' => 'Yao',     'phone' => '0701010101', 'credit_limit' => 150000],
            ['identifier' => 'AGR-002', 'firstname' => 'Aminata',  'lastname' => 'Coulibaly','phone' => '0702020202', 'credit_limit' => 200000],
            ['identifier' => 'AGR-003', 'firstname' => 'Dramane',  'lastname' => 'Koné',     'phone' => '0703030303', 'credit_limit' => 100000],
            ['identifier' => 'AGR-004', 'firstname' => 'Fatimata', 'lastname' => 'Traoré',   'phone' => '0704040404', 'credit_limit' => 250000],
            ['identifier' => 'AGR-005', 'firstname' => 'Bamba',    'lastname' => 'Diallo',   'phone' => '0705050505', 'credit_limit' => 75000],
        ];

        foreach ($farmers as $f) {
            Farmer::create($f);
        }
    }
}