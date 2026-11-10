<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id_user
 * @property string $nama
 * @property string $email
 * @property string $role
 * @property string|null $password_hash
 * @property string $status
 * @property int|null $id_company
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nama',
        'email',
        'role',
        'password_hash',
        'status',
        'id_company',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company', 'id_company');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'users_id_user', 'id_user');
    }
}
