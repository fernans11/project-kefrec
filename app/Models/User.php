<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'usertype', // penting
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * INI KUNCI MASALAH 403
     * Tentukan siapa yang boleh masuk Filament
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->usertype, ['admin', 'owner']);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'user_id');
    }

    protected static function booted(): void
    {
        static::saved(function (User $user): void {
            if ($user->isEmployeeRole()) {
                $user->customer()->delete();
            }
        });
    }

    public function isEmployeeRole(): bool
    {
        return in_array($this->usertype, ['cashier', 'kasir', 'kitchen', 'dapur'], true);
    }
}
