<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\DocumentVerification;
use App\Models\Inspection;
use App\Models\InspectorProfile;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\User;
use App\Services\Inspector\InspectionModeAdvisor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('SYM-22')]
class InspectionAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $inspector;

    protected User $otherInspector;

    protected User $seller;

    protected User $buyer;

    protected Category $category;

    protected Category $highRiskCategory;

    protected Company $sellerCompany;

    protected Company $buyerCompany;

    protected PurchaseOrder $purchaseOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $this->sellerCompany = Company::factory()->seller()->create(['alamat' => 'Bandung, Jawa Barat']);
        $this->buyerCompany = Company::factory()->buyer()->create();

        $this->seller = User::factory()->seller($this->sellerCompany)->create();
        $this->buyer = User::factory()->buyer($this->buyerCompany)->create();

        $this->category = Category::factory()->create([
            'nama' => 'Perca Katun',
            'tingkat_risiko' => 'RENDAH',
            'wajib_sertifikat_lab' => 0,
        ]);

        $this->highRiskCategory = Category::factory()->create([
            'nama' => 'Slag Baja',
            'tingkat_risiko' => 'TINGGI',
            'wajib_sertifikat_lab' => 1,
        ]);

        $this->inspector = User::factory()->inspector()->create();
        InspectorProfile::factory()->create([
            'id_user' => $this->inspector->id_user,
            'bidang_keahlian' => [$this->category->id_categories, $this->highRiskCategory->id_categories],
            'wilayah_tugas' => 'Jawa Barat',
            'status_aktif' => 1,
            'berlaku_sampai' => now()->addYear(),
        ]);

        $this->otherInspector = User::factory()->inspector()->create();
        InspectorProfile::factory()->create([
            'id_user' => $this->otherInspector->id_user,
            'bidang_keahlian' => [$this->category->id_categories],
            'wilayah_tugas' => 'Jawa Timur',
            'status_aktif' => 1,
            'berlaku_sampai' => now()->addYear(),
        ]);

        $listing = Listing::factory()->create([
            'companies_id_company' => $this->sellerCompany->id_company,
            'categories_id_categories' => $this->category->id_categories,
        ]);

        $contract = RfqContract::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'listings_id_listings' => $listing->id_listings,
        ]);

        $this->purchaseOrder = PurchaseOrder::factory()->create([
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
            'rfq_contracts_id_rfq_contract' => $contract->id_rfq_contract,
            'volume_kg' => 5000.00,
            'status' => OrderStatus::DIKONFIRMASI,
        ]);
    }

    public function test_admin_dapat_melihat_daftar_order_menunggu_penugasan_dan_riwayat_inspeksi(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.inspections.index'));

        $response->assertOk();
        $response->assertSee('Penugasan Inspeksi');
        $response->assertSee('PO #'.$this->purchaseOrder->id_purchase_order);
        $response->assertSee($this->sellerCompany->nama);
    }

    public function test_admin_dapat_melihat_halaman_form_penugasan_dengan_rekomendasi_mode_desk(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.inspections.create', $this->purchaseOrder));

        $response->assertOk();
        $response->assertSee('PO #'.$this->purchaseOrder->id_purchase_order);
        $response->assertSee('Mode DESK');
        $response->assertSee($this->inspector->nama);
        $response->assertSee('Kategori Sesuai');
    }

    public function test_advisor_merekomendasikan_mode_lapangan_untuk_kategori_risiko_tinggi_atau_volume_besar(): void
    {
        $advisor = new InspectionModeAdvisor;

        // 1. Order risiko rendah & volume normal -> DESK
        $recDesk = $advisor->recommendMode($this->purchaseOrder);
        $this->assertEquals(Inspection::MODE_DESK, $recDesk['mode']);

        // 2. Order dengan volume besar (> 10 ton) -> LAPANGAN
        $this->purchaseOrder->volume_kg = 15000.00;
        $this->purchaseOrder->save();
        $recHighVol = $advisor->recommendMode($this->purchaseOrder);
        $this->assertEquals(Inspection::MODE_LAPANGAN, $recHighVol['mode']);

        // 3. Order dengan kategori risiko tinggi -> LAPANGAN
        $highRiskListing = Listing::factory()->create([
            'companies_id_company' => $this->sellerCompany->id_company,
            'categories_id_categories' => $this->highRiskCategory->id_categories,
        ]);
        $highRiskContract = RfqContract::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'listings_id_listings' => $highRiskListing->id_listings,
        ]);
        $highRiskPo = PurchaseOrder::factory()->create([
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
            'rfq_contracts_id_rfq_contract' => $highRiskContract->id_rfq_contract,
            'volume_kg' => 2000.00,
            'status' => OrderStatus::DIKONFIRMASI,
        ]);
        $recHighRisk = $advisor->recommendMode($highRiskPo);
        $this->assertEquals(Inspection::MODE_LAPANGAN, $recHighRisk['mode']);
    }

    public function test_admin_dapat_menugaskan_inspector_dengan_mode_dan_jadwal_valid(): void
    {
        $jadwal = now()->addDays(3)->setHour(9)->setMinute(0)->format('Y-m-d H:i:s');

        $payload = [
            'users_id_user' => $this->inspector->id_user,
            'mode_verifikasi' => Inspection::MODE_DESK,
            'is_override' => '0',
            'jadwal' => $jadwal,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.inspections.store', $this->purchaseOrder), $payload);

        $response->assertRedirect(route('admin.inspections.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inspections', [
            'purchase_orders_id_purchase_order' => $this->purchaseOrder->id_purchase_order,
            'users_id_user' => $this->inspector->id_user,
            'mode_verifikasi' => Inspection::MODE_DESK,
            'status' => Inspection::STATUS_DITUGASKAN,
        ]);
    }

    public function test_penugasan_menginisialisasi_verifikasi_dokumen_dan_mengubah_status_po_ke_inspeksi(): void
    {
        $jadwal = now()->addDays(2)->setHour(14)->setMinute(0)->format('Y-m-d H:i:s');

        $payload = [
            'users_id_user' => $this->inspector->id_user,
            'mode_verifikasi' => Inspection::MODE_DESK,
            'is_override' => '0',
            'jadwal' => $jadwal,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.inspections.store', $this->purchaseOrder), $payload);

        // Verifikasi dokumen terbuat berstatus MENUNGGU
        $this->assertDatabaseHas('document_verifications', [
            'purchase_orders_id_purchase_order' => $this->purchaseOrder->id_purchase_order,
            'users_id_user' => $this->inspector->id_user,
            'status' => DocumentVerification::STATUS_MENUNGGU,
        ]);

        // Status PO berubah menjadi INSPEKSI
        $this->purchaseOrder->refresh();
        $this->assertEquals(OrderStatus::INSPEKSI, $this->purchaseOrder->status);
    }

    public function test_override_mode_gagal_bila_alasan_mode_kosong(): void
    {
        $payload = [
            'users_id_user' => $this->inspector->id_user,
            'mode_verifikasi' => Inspection::MODE_LAPANGAN, // Berbeda dari rekomendasi DESK
            'is_override' => '1',
            'alasan_mode' => '',
            'jadwal' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.inspections.create', $this->purchaseOrder))
            ->post(route('admin.inspections.store', $this->purchaseOrder), $payload);

        $response->assertRedirect(route('admin.inspections.create', $this->purchaseOrder));
        $response->assertSessionHasErrors(['alasan_mode']);
    }

    public function test_penugasan_gagal_bila_jadwal_bentrok_dengan_inspeksi_lain(): void
    {
        $bentrokJadwal = now()->addDays(4)->setHour(10)->setMinute(0);

        // Buat inspeksi existing untuk inspector yang sama pada hari yang sama
        Inspection::factory()->create([
            'users_id_user' => $this->inspector->id_user,
            'jadwal' => $bentrokJadwal,
            'status' => Inspection::STATUS_DITUGASKAN,
        ]);

        $payload = [
            'users_id_user' => $this->inspector->id_user,
            'mode_verifikasi' => Inspection::MODE_DESK,
            'is_override' => '0',
            'jadwal' => $bentrokJadwal->copy()->setHour(14)->format('Y-m-d H:i:s'), // jam beda, hari sama
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.inspections.create', $this->purchaseOrder))
            ->post(route('admin.inspections.store', $this->purchaseOrder), $payload);

        $response->assertRedirect(route('admin.inspections.create', $this->purchaseOrder));
        $response->assertSessionHasErrors(['jadwal']);
    }

    public function test_admin_dapat_membatalkan_penugasan_inspeksi(): void
    {
        $inspection = Inspection::factory()->create([
            'purchase_orders_id_purchase_order' => $this->purchaseOrder->id_purchase_order,
            'users_id_user' => $this->inspector->id_user,
            'status' => Inspection::STATUS_DITUGASKAN,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.inspections.cancel', $inspection), [
                'alasan_batal' => 'Perubahan jadwal kesepakatan mitra.',
            ]);

        $response->assertRedirect(route('admin.inspections.index'));
        $response->assertSessionHas('success');

        $inspection->refresh();
        $this->assertEquals(Inspection::STATUS_DIBATALKAN, $inspection->status);
    }

    public function test_pengguna_bukan_admin_ditolak_mengakses_penugasan_inspeksi(): void
    {
        // Seller ditolak
        $this->actingAs($this->seller)
            ->get(route('admin.inspections.index'))
            ->assertForbidden();

        // Buyer ditolak
        $this->actingAs($this->buyer)
            ->get(route('admin.inspections.create', $this->purchaseOrder))
            ->assertForbidden();

        // Inspector ditolak membuat penugasan
        $this->actingAs($this->inspector)
            ->post(route('admin.inspections.store', $this->purchaseOrder), [])
            ->assertForbidden();
    }

    public function test_inspector_dapat_melihat_tugas_inspeksi_miliknya(): void
    {
        Inspection::factory()->create([
            'purchase_orders_id_purchase_order' => $this->purchaseOrder->id_purchase_order,
            'users_id_user' => $this->inspector->id_user,
            'status' => Inspection::STATUS_DITUGASKAN,
        ]);

        $response = $this->actingAs($this->inspector)
            ->get(route('inspector.tasks.index'));

        $response->assertOk();
        $response->assertSee('Tugas Inspeksi Saya');
        $response->assertSee('PO #'.$this->purchaseOrder->id_purchase_order);
    }
}
