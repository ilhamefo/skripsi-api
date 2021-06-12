<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = ['categoryName', 'categoryIcon', 'user_id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function products()
    {
        // this line is if we're using pivot table,
        // but I decided to not use it
        // (not deleting this line bcs it's 'nice to have' :D)

        // return $this->belongsToMany(Products::class, 'products_type_pivot', 'product_category_id', 'product_id');
    }
}
