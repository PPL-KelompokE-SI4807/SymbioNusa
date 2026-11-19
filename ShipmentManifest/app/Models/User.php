<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/** Menimpa User bawaan Laravel: menambah role dan company_id (FND-01 / NFR-04). */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_SELLER = 'SELLER';
    public const ROLE_BUYER = 'BUYER';
    public const ROLE_INSPECTOR = 'INSPECTOR';

    protected $fillable = ['name', 'email', 'password', 'role', 'company_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin / DLH',
            self::ROLE_SELLER => 'Seller',
            self::ROLE_BUYER => 'Buyer',
            self::ROLE_INSPECTOR => 'Inspector',
            default => (string) $this->role,
        };
    }
}
