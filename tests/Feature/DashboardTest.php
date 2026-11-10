<?php

namespace Tests\Feature;

use App\Livewire\DashboardOverview;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * FND-04 (SYM-28): Main Dashboard per peran.
 *
 * Memakai skema DDL MySQL yang sebenarnya; setiap tes dibungkus transaksi
 * sehingga data uji tidak tertinggal di basis data.
 */
#[Group('SYM-28')]
class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_dashboard_and_view_metrics(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@symbionusa.test'],
            [
                'nama' => 'Super Admin DLH',
                'role' => 'ADMIN',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
            ]
        );

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Hak Akses: ADMIN');
        $response->assertSee('Selamat Datang, Super Admin DLH!');
        $response->assertSee('Akumulasi Dampak Lingkungan');
        $response->assertSee('Antrean Verifikasi Perusahaan');
    }

    public function test_seller_can_access_dashboard_and_view_metrics(): void
    {
        $sellerCompany = Company::firstOrCreate(
            ['nib' => '8888777766665'],
            [
                'nama' => 'PT Seller Mandiri',
                'tipe' => 'SELLER',
                'alamat' => 'Bekasi',
                'kontak' => '021999888',
                'status_verifikasi' => 'TERVERIFIKASI',
            ]
        );

        $seller = User::firstOrCreate(
            ['email' => 'seller_test@symbionusa.test'],
            [
                'nama' => 'Budi Seller Mandiri',
                'role' => 'SELLER',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
                'id_company' => $sellerCompany->id_company,
            ]
        );

        $response = $this->actingAs($seller)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Hak Akses: SELLER');
        $response->assertSee('Selamat Datang, Budi Seller Mandiri!');
        $response->assertSee('Katalog Listing Sisa Produksi Saya');
        $response->assertSee('Listing Aktif Saya');
    }

    public function test_buyer_can_access_dashboard_and_view_metrics(): void
    {
        $buyerCompany = Company::firstOrCreate(
            ['nib' => '7777666655554'],
            [
                'nama' => 'PT Buyer Daur Ulang',
                'tipe' => 'BUYER',
                'alamat' => 'Cilegon',
                'kontak' => '0254999111',
                'status_verifikasi' => 'TERVERIFIKASI',
            ]
        );

        $buyer = User::firstOrCreate(
            ['email' => 'buyer_test@symbionusa.test'],
            [
                'nama' => 'Rina Buyer Sekunder',
                'role' => 'BUYER',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
                'id_company' => $buyerCompany->id_company,
            ]
        );

        $response = $this->actingAs($buyer)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Hak Akses: BUYER');
        $response->assertSee('Selamat Datang, Rina Buyer Sekunder!');
        $response->assertSee('Kebutuhan Bahan Baku Aktif');
        $response->assertSee('Pesanan Pembelian Saya');
    }

    public function test_inspector_can_access_dashboard_and_view_metrics(): void
    {
        $inspector = User::firstOrCreate(
            ['email' => 'inspector_test@symbionusa.test'],
            [
                'nama' => 'Dr. Hendra Inspector Mutu',
                'role' => 'INSPECTOR',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
            ]
        );

        $response = $this->actingAs($inspector)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Hak Akses: INSPECTOR');
        $response->assertSee('Selamat Datang, Dr. Hendra Inspector Mutu!');
        $response->assertSee('Penugasan Verifikasi Mutu');
        $response->assertSee('Inspeksi Ditugaskan');
    }

    public function test_dashboard_renders_livewire_component_correctly(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@symbionusa.test'],
            [
                'nama' => 'Super Admin DLH',
                'role' => 'ADMIN',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
            ]
        );

        $this->actingAs($admin);

        Livewire::test(DashboardOverview::class)
            ->assertSee('Hak Akses: ADMIN')
            ->assertSee('Selamat Datang, Super Admin DLH!')
            ->assertStatus(200);
    }
}
