<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name', 'unit', 'stock', 'min_stock',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Product::class)
            ->withPivot(['qty'])
            ->withTimestamps();
    }
}
