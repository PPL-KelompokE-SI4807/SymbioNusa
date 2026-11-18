<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * User sesuai tabel `users` di DDL (nama, email, role, password_hash, status, id_company).
 *
 * Contoh:
 *   User::factory()->admin()->create();
 *   User::factory()->seller()->create();              // sekaligus membuat perusahaan SELLER terverifikasi
 *   User::factory()->buyer($company)->create();       // memakai perusahaan yang sudah ada
 *   User::factory()->inspector()->create();
 *
 * Kata sandi semua user buatan factory: "password".
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Hash kata sandi disimpan sekali supaya test tidak lambat.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake('id_ID')->name(),
            'email' => 'uji.'.fake()->unique()->userName().'@symbionusa.test',
            'role' => 'BUYER',
            'password_hash' => static::$password ??= Hash::make('password'),
            'status' => 'AKTIF',
            'id_company' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'ADMIN', 'id_company' => null]);
    }

    public function inspector(): static
    {
        return $this->state(fn () => ['role' => 'INSPECTOR', 'id_company' => null]);
    }

    /**
     * Seller; tanpa argumen, factory membuat perusahaan SELLER terverifikasi.
     */
    public function seller(?Company $company = null): static
    {
        return $this->state(fn () => [
            'role' => 'SELLER',
            'id_company' => $company?->id_company ?? Company::factory()->seller(),
        ]);
    }

    /**
     * Buyer; tanpa argumen, factory membuat perusahaan BUYER terverifikasi.
     */
    public function buyer(?Company $company = null): static
    {
        return $this->state(fn () => [
            'role' => 'BUYER',
            'id_company' => $company?->id_company ?? Company::factory()->buyer(),
        ]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['status' => 'NONAKTIF']);
    }
}
