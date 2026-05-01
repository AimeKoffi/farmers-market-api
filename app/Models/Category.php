<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id', 'depth'];

    // Catégorie parente
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Sous-catégories directes
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Toutes les sous-catégories récursivement
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    // Produits dans cette catégorie
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}