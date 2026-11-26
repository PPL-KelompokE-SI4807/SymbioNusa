<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class DashboardMockedTest extends TestCase
{
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_controller_renders_successfully_with_mocked_user(): void
    {
        $company = new Company([
            'id_company' => 1,
            'nama' => 'PT Plastik Makmur',
            'tipe' => 'SELLER',
            'status_verifikasi' => 'TERVERIFIKASI',
        ]);

        $user = new User([
            'id_user' => 1,
            'nama' => 'Budi Seller',
            'email' => 'seller@symbionusa.test',
            'role' => 'SELLER',
            'status' => 'AKTIF',
            'id_company' => 1,
        ]);
        $user->setRelation('company', $company);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Budi Seller');
    }

    public function test_admin_dashboard_renders_successfully(): void
    {
        $user = new User([
            'id_user' => 2,
            'nama' => 'Admin DLH',
            'email' => 'admin@symbionusa.test',
            'role' => 'ADMIN',
            'status' => 'AKTIF',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin DLH');
    }

    public function test_buyer_dashboard_renders_successfully(): void
    {
        $company = new Company([
            'id_company' => 2,
            'nama' => 'PT Daur Ulang',
            'tipe' => 'BUYER',
            'status_verifikasi' => 'TERVERIFIKASI',
        ]);

        $user = new User([
            'id_user' => 3,
            'nama' => 'Siti Buyer',
            'email' => 'buyer@symbionusa.test',
            'role' => 'BUYER',
            'status' => 'AKTIF',
            'id_company' => 2,
        ]);
        $user->setRelation('company', $company);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Siti Buyer');
    }

    public function test_inspector_dashboard_renders_successfully(): void
    {
        $user = new User([
            'id_user' => 4,
            'nama' => 'Ir. Joko',
            'email' => 'inspector@symbionusa.test',
            'role' => 'INSPECTOR',
            'status' => 'AKTIF',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Ir. Joko');
    }
}
