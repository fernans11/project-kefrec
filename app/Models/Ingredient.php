<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name', 'unit', 'stock', 'min_stock',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Product::class)
            ->withPivot(['qty'])
            ->withTimestamps();
    }
}
