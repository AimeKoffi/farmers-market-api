<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $herbicides  = Category::where('name', 'Herbicides')->first();
        $insecticides= Category::where('name', 'Insecticides')->first();
        $npk         = Category::where('name', 'Engrais NPK')->first();
        $bio         = Category::where('name', 'Engrais Bio')->first();
        $cacao       = Category::where('name', 'Semences Cacao')->first();

        $products = [
            ['name' => 'Glyphosate 1L',       'category_id' => $herbicides->id,   'price_fcfa' => 3500],
            ['name' => 'Herbi-Stop 500ml',     'category_id' => $herbicides->id,   'price_fcfa' => 2200],
            ['name' => 'Lambda-Force 250ml',   'category_id' => $insecticides->id, 'price_fcfa' => 4800],
            ['name' => 'Diméthoate 1L',        'category_id' => $insecticides->id, 'price_fcfa' => 3200],
            ['name' => 'NPK 15-15-15 (50kg)',  'category_id' => $npk->id,          'price_fcfa' => 18500],
            ['name' => 'NPK 20-10-10 (25kg)',  'category_id' => $npk->id,          'price_fcfa' => 9500],
            ['name' => 'Compost Premium 20kg', 'category_id' => $bio->id,          'price_fcfa' => 7500],
            ['name' => 'Semence Cacao Hybride','category_id' => $cacao->id,        'price_fcfa' => 12000],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }
    }
}