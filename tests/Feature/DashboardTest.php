<?php

namespace Tests\Feature;

use App\Livewire\DashboardOverview;
use App\Models\Company;
use App\Models\ImpactLog;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
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
        $response->assertSee('dash-senja');
        // Varian Kaca (DESIGN.md, amandemen FND v12): hanya dasbor Admin, susunan mengikuti SymbioNusa Lab.
        $response->assertSee('dash-kaca');
        $response->assertSee('kaca-backdrop', false);
        // Pembungkus bersama x-app.kaca membawa tombol jeda gerak latar (WCAG 2.2.2).
        $response->assertSee('kaca-still-toggle', false);
        $response->assertSee('Jeda gerak latar');
        $response->assertSeeInOrder([
            'Dashboard', 'Perbandingan periode', 'Perlu keputusan Anda', 'Inspeksi', 'Sungai Order', 'Peta pengawasan',
            'Umur tagihan', 'Aktivitas terbaru', 'Beban Inspector', 'Tonase per kategori', 'Dampak lingkungan tercatat',
        ]);
        // Panel yang DDL belum mendukung memakai contoh dan wajib bertanda Data demo.
        $response->assertSee('Data demo');
        $response->assertSee('data-sidebar', false);
        // Label diagnosis tanpa dasar ambang dilarang.
        $response->assertDontSee('tertahan');
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
        $response->assertSee('Budi Seller Mandiri');
        $response->assertSee('dash-senja');
        $response->assertDontSee('dash-kaca');
        $response->assertDontSee('kaca-backdrop', false);
        $response->assertSeeInOrder(['Perlu tindakan Anda', 'Sedang berjalan', 'Gambaran total tercatat']);
        $response->assertSee('Stok listing aktif');
        $response->assertSee('Order Anda per tahap');
        $response->assertSee('Listing aktif Anda');
        $response->assertDontSee('tertahan');
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
        $response->assertSee('Rina Buyer Sekunder');
        $response->assertSee('dash-senja');
        $response->assertSeeInOrder(['Perlu tindakan Anda', 'Sedang berjalan', 'Pengiriman ke Anda', 'Gambaran total tercatat']);
        $response->assertSee('Tagihan terbuka');
        $response->assertSee('Pembelian Anda per tahap');
        $response->assertSee('Material yang bisa Anda beli');
        $response->assertDontSee('tertahan');
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
        $response->assertSee('Dr. Hendra Inspector Mutu');
        $response->assertSee('dash-senja');
        $response->assertSeeInOrder(['Perlu tindakan Anda', 'Sedang berjalan', 'Gambaran total tercatat', 'Kredensial Anda']);
        $response->assertSee('Jadwal 7 hari');
        $response->assertSee('Inspeksi selesai');
        $response->assertDontSee('tertahan');
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
            ->assertSee('Sungai Order')
            ->assertSee('Perlu keputusan Anda')
            ->assertStatus(200);
    }

    public function test_admin_queue_lists_company_waiting_for_verification(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@symbionusa.test'],
            ['nama' => 'Super Admin DLH', 'role' => 'ADMIN', 'password_hash' => Hash::make('password'), 'status' => 'AKTIF']
        );
        Company::firstOrCreate(
            ['nib' => '1212343456567'],
            ['nama' => 'CV Antre Verifikasi', 'tipe' => 'SELLER', 'alamat' => 'Bandung', 'kontak' => '022111', 'status_verifikasi' => 'PENDING']
        );

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('CV Antre Verifikasi')
            ->assertSee('Verifikasi Mitra');
    }

    public function test_admin_flow_counts_rows_and_queue_come_from_one_source(): void
    {
        $data = app(DashboardService::class)->admin();

        $realCounts = PurchaseOrder::query()->selectRaw('status, COUNT(*) AS n')->groupBy('status')->pluck('n', 'status');

        // 10 tahap STATUS_MAP dalam urutan tetap, lalu 4 jalur keluar dari tahap asal yang benar.
        $this->assertSame(
            ['DRAFT', 'MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'INSPEKSI', 'LOLOS_INSPEKSI', 'INVOICED', 'DIBAYAR', 'DALAM_PENGIRIMAN', 'DITERIMA', 'SELESAI'],
            array_column($data['flow']['stages'], 'key')
        );
        $this->assertSame(
            ['DITOLAK' => 'MENUNGGU_KONFIRMASI', 'DIBATALKAN' => 'DIKONFIRMASI', 'GAGAL_INSPEKSI' => 'INSPEKSI', 'SENGKETA' => 'DITERIMA'],
            array_column($data['flow']['exits'], 'origin', 'key')
        );

        foreach ([...$data['flow']['stages'], ...$data['flow']['exits']] as $stage) {
            // Hitungan segmen = total sebenarnya di DB; baris panel = hitungan (atau batas muat, dinyatakan "Menampilkan").
            $this->assertSame((int) ($realCounts[$stage['key']] ?? 0), $stage['count'], $stage['key']);
            $this->assertSame(min($stage['count'], $data['flow']['list_limit']), count($stage['orders']), $stage['key']);
        }

        foreach ($data['queue']['groups'] as $group) {
            $this->assertGreaterThan(0, $group['count']);
            $this->assertSame(min($group['count'], 25), count($group['items']), $group['key']);
            // Item tanpa halaman tujuan tidak boleh berbentuk aksi.
            foreach ($group['items'] as $item) {
                $this->assertTrue($item['url'] === null || $group['action'] !== null, $group['key']);
            }
        }
    }

    public function test_seller_flow_counts_are_scoped_to_the_seller_company(): void
    {
        $companyId = (int) PurchaseOrder::query()->value('companies_id_seller');
        if ($companyId === 0) {
            $this->markTestSkipped('Tidak ada order di basis data uji.');
        }

        $data = app(DashboardService::class)->seller($companyId);
        $real = PurchaseOrder::where('companies_id_seller', $companyId)->selectRaw('status, COUNT(*) AS n')->groupBy('status')->pluck('n', 'status');

        foreach ([...$data['flow']['stages'], ...$data['flow']['exits']] as $stage) {
            $this->assertSame((int) ($real[$stage['key']] ?? 0), $stage['count'], $stage['key']);
        }
        // Kelompok "Konfirmasi order masuk" memakai hitungan tahap Menunggu Konfirmasi yang sama.
        $confirm = collect($data['queue']['groups'])->firstWhere('key', 'konfirmasi');
        $this->assertSame((int) ($real['MENUNGGU_KONFIRMASI'] ?? 0), $confirm['count'] ?? 0);
        foreach ($data['queue']['groups'] as $group) {
            foreach ($group['items'] as $item) {
                $this->assertTrue($item['url'] === null || $group['action'] !== null, $group['key']);
            }
        }
    }

    public function test_admin_material_shares_use_full_total(): void
    {
        $materials = app(DashboardService::class)->admin()['materials'];

        $this->assertEqualsWithDelta((float) ImpactLog::sum('tonase_ton'), $materials['total_ton'], 0.001);
        if ($materials['rows'] !== []) {
            $this->assertEqualsWithDelta(1.0, array_sum(array_column($materials['rows'], 'share')), 0.0001);
            $this->assertEqualsWithDelta($materials['total_ton'], array_sum(array_column($materials['rows'], 'ton')), 0.001);
        }
    }

    public function test_seller_without_company_sees_profile_hint_instead_of_data(): void
    {
        $seller = User::firstOrCreate(
            ['email' => 'seller_no_company@symbionusa.test'],
            ['nama' => 'Seller Tanpa Perusahaan', 'role' => 'SELLER', 'password_hash' => Hash::make('password'), 'status' => 'AKTIF']
        );

        $this->actingAs($seller)->get('/dashboard')
            ->assertOk()
            ->assertSee('Akun belum terhubung ke perusahaan')
            ->assertDontSee('Order masuk');
    }
}
