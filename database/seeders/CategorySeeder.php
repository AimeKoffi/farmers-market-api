<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Niveau 0 — catégories racines
        $pesticides  = Category::create(['name' => 'Pesticides',  'depth' => 0]);
        $fertilizers = Category::create(['name' => 'Engrais',     'depth' => 0]);
        $seeds       = Category::create(['name' => 'Semences',    'depth' => 0]);

        // Niveau 1 — sous-catégories
        Category::create(['name' => 'Herbicides',    'parent_id' => $pesticides->id,  'depth' => 1]);
        Category::create(['name' => 'Insecticides',  'parent_id' => $pesticides->id,  'depth' => 1]);
        Category::create(['name' => 'Fongicides',    'parent_id' => $pesticides->id,  'depth' => 1]);
        Category::create(['name' => 'Engrais NPK',   'parent_id' => $fertilizers->id, 'depth' => 1]);
        Category::create(['name' => 'Engrais Bio',   'parent_id' => $fertilizers->id, 'depth' => 1]);
        Category::create(['name' => 'Semences Cacao','parent_id' => $seeds->id,       'depth' => 1]);
        Category::create(['name' => 'Semences Maïs', 'parent_id' => $seeds->id,       'depth' => 1]);
    }
}