<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // jika nama tabel "transactions" (default plural), tidak perlu diisi apa-apa
    // kalau berbeda, tambahkan:
    // protected $table = 'nama_tabel_kamu';

    protected $fillable = [
        'user_id',
        'total_amount',
        'paid_at',
        // tambahkan field lain sesuai kebutuhan
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];
}
