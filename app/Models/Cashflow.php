<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    protected $fillable = [
        'date',
        'type',
        'category',
        'amount',
        'source',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'integer',
    ];
}
