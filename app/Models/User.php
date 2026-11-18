<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /**
     * FND-03 (SYM-3): kirim email reset kata sandi berbahasa Indonesia.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company', 'id_company');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'users_id_user', 'id_user');
    }

    public function activeRole(): string
    {
        $baseRole = strtoupper($this->role ?? 'BUYER');

        if (
            in_array($baseRole, ['SELLER', 'BUYER'], true)
            && $this->company?->tipe === Company::TYPE_BOTH
            && session()->has('active_role')
        ) {
            $sessionRole = strtoupper((string) session('active_role'));
            if (in_array($sessionRole, ['SELLER', 'BUYER'], true)) {
                return $sessionRole;
            }
        }

        return $baseRole;
    }

    /**
     * Profil kompetensi bila user ber-role INSPECTOR (PBI-01 / SYM-5).
     *
     * @return HasOne<InspectorProfile, $this>
     */
    public function inspectorProfile(): HasOne
    {
        return $this->hasOne(InspectorProfile::class, 'id_user', 'id_user');
    }
}
