<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
    protected $table = 'users';

    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $guarded = [];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function company(): HasMany
    {
        return $this->hasMany(Company::class, 'id_company', 'id_company');
    }
}
