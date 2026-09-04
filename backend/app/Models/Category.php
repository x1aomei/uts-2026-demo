<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    /**
     * Relasi ke produk yang ada di kategori ini.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relasi ke kategori induk (Parent Category).
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relasi ke sub-kategori di bawahnya (Child Categories).
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}