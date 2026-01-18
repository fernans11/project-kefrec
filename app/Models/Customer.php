<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Customer extends Model
{
    protected $fillable = [

        'name',
        'email',
        'phone',
        'address',
        'is_member',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }
}
