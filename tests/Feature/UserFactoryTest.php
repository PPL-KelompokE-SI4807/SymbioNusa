<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Factory User dan Company harus cocok dengan skema DDL MySQL supaya bisa dipakai test semua PBI.
 */
#[Group('SYM-28')]
class UserFactoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_factory_membuat_user_per_peran_sesuai_ddl(): void
    {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();

        $this->assertSame('ADMIN', $admin->fresh()->role);
        $this->assertSame('INSPECTOR', $inspector->fresh()->role);
        $this->assertNull($admin->id_company);
        $this->assertSame('AKTIF', $admin->fresh()->status);
        $this->assertTrue(Hash::check('password', $admin->fresh()->password_hash));
    }

    public function test_seller_dan_buyer_mendapat_perusahaan_terverifikasi(): void
    {
        $seller = User::factory()->seller()->create();
        $buyer = User::factory()->buyer()->create();

        $this->assertSame('SELLER', $seller->company->tipe);
        $this->assertSame('TERVERIFIKASI', $seller->company->status_verifikasi);
        $this->assertSame('BUYER', $buyer->company->tipe);
        $this->assertNotSame($seller->id_company, $buyer->id_company);
    }

    public function test_user_bisa_memakai_perusahaan_yang_sudah_ada(): void
    {
        $company = Company::factory()->pending()->create();

        $user = User::factory()->seller($company)->create();

        $this->assertSame($company->id_company, $user->id_company);
        $this->assertSame('PENDING', $company->fresh()->status_verifikasi);
    }

    public function test_user_factory_bisa_login(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
