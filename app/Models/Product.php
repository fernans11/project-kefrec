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
        'stock',
        'track_stock',
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

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (! array_key_exists('is_new', $product->getAttributes())) {
                $product->is_new = true;
            }
        });
    }

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

    public function completedTransactionItems(): HasMany
    {
        return $this->hasMany(\App\Models\TransactionItem::class)
            ->whereHas('transaction', fn ($query) => $query->where('status', 'completed'));
    }
}
