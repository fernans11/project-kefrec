<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'category',
        'name',
        'slug',
        'description',
        'price',
        'image_url',
        'is_active',
        'is_popular',
        'is_new',
        'rating',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'is_new' => 'boolean',
        'price' => 'integer',
        'rating' => 'float',
        'sort_order' => 'integer',
        'stock' => 'integer',
        'track_stock' => 'boolean',
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Ingredient::class)
            ->withPivot(['qty'])
            ->withTimestamps();
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(\App\Models\TransactionItem::class);
    }
}
