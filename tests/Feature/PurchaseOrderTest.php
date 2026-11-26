<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\RfqStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\RfqOffer;
use App\Models\User;
use App\Services\OrderStateMachine;
use DomainException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('SYM-9')]
class PurchaseOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected User $seller;

    protected User $buyer;

    protected User $otherUser;

    protected Company $sellerCompany;

    protected Company $buyerCompany;

    protected Company $otherCompany;

    protected Category $category;

    protected Listing $listing;

    protected RfqContract $agreedRfq;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerCompany = Company::factory()->seller()->create();
        $this->buyerCompany = Company::factory()->buyer()->create();
        $this->otherCompany = Company::factory()->buyer()->create();

        $this->seller = User::factory()->seller($this->sellerCompany)->create();
        $this->buyer = User::factory()->buyer($this->buyerCompany)->create();
        $this->otherUser = User::factory()->buyer($this->otherCompany)->create();

        $this->category = Category::factory()->create([
            'nama' => 'Perca Katun Industri',
            'status' => 'AKTIF',
            'is_b3_prohibited' => 0,
            'toleransi_susut_persen' => 5.00,
            'kadar_air_maks_persen' => 12.00,
            'toleransi_kadar_air_poin' => 2.00,
        ]);

        $this->listing = Listing::factory()->create([
            'companies_id_company' => $this->sellerCompany->id_company,
            'categories_id_categories' => $this->category->id_categories,
            'status' => 'PUBLISHED',
            'volume_kg' => 10000.00,
            'stok_kg' => 10000.00,
            'harga_per_kg' => 3500.00,
            'judul' => 'Kain Perca Katun Grade A',
        ]);

        $this->agreedRfq = RfqContract::create([
            'volume_kg_disepakati' => 2000.00,
            'harga_disepakati' => 3200.00,
            'tenggat' => now()->addDays(3),
            'status' => RfqStatus::DISEPAKATI,
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        RfqOffer::create([
            'volume_kg' => 2000.00,
            'harga_per_kg' => 3200.00,
            'catatan' => 'Harga deal disepakati',
            'urutan' => 1,
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);
    }

    /**
     * AC 1 & Layar 10, 11: Buyer dapat mengakses daftar PO dan formulir buat PO.
     */
    public function test_buyer_dapat_melihat_halaman_daftar_dan_form_buat_po(): void
    {
        $response = $this->actingAs($this->buyer)->get(route('po.index'));
        $response->assertOk();
        $response->assertSee('Purchase Order');
        $response->assertSee('+ Buat PO');

        $responseCreate = $this->actingAs($this->buyer)->get(route('po.create', ['rfq_id' => $this->agreedRfq->id_rfq_contract]));
        $responseCreate->assertOk();
        $responseCreate->assertSee('Buat Purchase Order');
        $responseCreate->assertSee('Kain Perca Katun Grade A');
    }

    /**
     * AC 1, AC 2, AC 3: Buyer membuat PO, menyalin snapshot toleransi kategori, dan mencatat riwayat awal.
     */
    public function test_buyer_dapat_membuat_po_dengan_snapshot_toleransi_dan_kirim_ke_seller(): void
    {
        $response = $this->actingAs($this->buyer)->post(route('po.store'), [
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'jarak_km' => 42.5,
            'action' => 'send',
        ]);

        $po = PurchaseOrder::where('rfq_contracts_id_rfq_contract', $this->agreedRfq->id_rfq_contract)->first();
        $this->assertNotNull($po);

        $response->assertRedirect(route('po.show', $po));

        // Verifikasi AC 1: Nilai nominal diambil dari kesepakatan RFQ
        $this->assertEquals('2000.00', $po->volume_kg);
        $this->assertEquals('3200.00', $po->harga_per_kg);
        $this->assertEquals('42.50', $po->jarak_km);
        $this->assertEquals(OrderStatus::MENUNGGU_KONFIRMASI, $po->status);

        // Verifikasi AC 2: Snapshot toleransi kategori tersalin
        $this->assertEquals('5.00', $po->toleransi_persen);
        $this->assertEquals('12.00', $po->kadar_air_maks_persen);
        $this->assertEquals('2.00', $po->toleransi_kadar_air_poin);
        $this->assertEquals(1900.0, $po->minVolumeKg());
        $this->assertEquals(2100.0, $po->maxVolumeKg());
        $this->assertEquals(6400000.0, $po->totalNominal());

        // Verifikasi AC 3: Riwayat order status history tercatat lengkap
        $this->assertDatabaseHas('order_status_history', [
            'purchase_orders_id_purchase_order' => $po->id_purchase_order,
            'dari' => null,
            'ke' => OrderStatus::DRAFT->value,
            'users_id_user' => $this->buyer->id_user,
        ]);

        $this->assertDatabaseHas('order_status_history', [
            'purchase_orders_id_purchase_order' => $po->id_purchase_order,
            'dari' => OrderStatus::DRAFT->value,
            'ke' => OrderStatus::MENUNGGU_KONFIRMASI->value,
            'users_id_user' => $this->buyer->id_user,
        ]);
    }

    /**
     * AC 1 & AC 8: PO tidak dapat dibuat jika RFQ belum disepakati atau sudah memiliki PO.
     */
    public function test_tidak_dapat_membuat_po_dari_rfq_belum_disepakati_atau_duplikat(): void
    {
        $unagreedRfq = RfqContract::create([
            'volume_kg_disepakati' => null,
            'harga_disepakati' => null,
            'tenggat' => now()->addDays(3),
            'status' => RfqStatus::NEGOSIASI,
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        $response = $this->actingAs($this->buyer)->post(route('po.store'), [
            'rfq_contracts_id_rfq_contract' => $unagreedRfq->id_rfq_contract,
            'action' => 'draft',
        ]);
        $response->assertSessionHasErrors('rfq_contracts_id_rfq_contract');

        // Buat PO pertama yang valid
        $this->actingAs($this->buyer)->post(route('po.store'), [
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'action' => 'draft',
        ]);

        // Daftar RFQ harus menampilkan tombol 'Lihat PO' dan tidak lagi menampilkan 'Buat PO'
        $responseRfqIndex = $this->actingAs($this->buyer)->get(route('rfq.index'));
        $responseRfqIndex->assertOk();
        $responseRfqIndex->assertSee('Lihat PO');

        // Coba buat PO kedua untuk RFQ yang sama (harus ditolak karena unik)
        $responseDuplicate = $this->actingAs($this->buyer)->post(route('po.store'), [
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'action' => 'draft',
        ]);
        $responseDuplicate->assertSessionHasErrors('rfq_contracts_id_rfq_contract');
    }

    /**
     * AC 3: OrderStateMachine memvalidasi aturan transisi dan mencatat ke audit log order_status_history.
     */
    public function test_order_state_machine_menjaga_aturan_transisi_dan_mencatat_riwayat(): void
    {
        $po = PurchaseOrder::factory()->draft()->create([
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
        ]);

        // Transisi tidak valid: DRAFT langsung ke SELESAI
        $this->expectException(DomainException::class);
        OrderStateMachine::transition($po, OrderStatus::SELESAI, $this->buyer);
    }

    /**
     * AC 4: Seller dapat mengonfirmasi PO, row listing di-lock, dan stok berkurang.
     */
    public function test_seller_mengonfirmasi_po_dan_stok_listing_direservasi(): void
    {
        $po = PurchaseOrder::factory()->menungguKonfirmasi()->create([
            'volume_kg' => '2500.00',
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
        ]);

        $stokAwal = (float) $this->listing->stok_kg; // 10000.00

        $response = $this->actingAs($this->seller)->post(route('po.confirm', $po));
        $response->assertRedirect(route('po.show', $po));
        $response->assertSessionHas('status');

        $po->refresh();
        $this->listing->refresh();

        // Status PO berubah ke DIKONFIRMASI
        $this->assertEquals(OrderStatus::DIKONFIRMASI, $po->status);

        // Stok listing berkurang sejumlah volume PO
        $this->assertEquals($stokAwal - 2500.00, (float) $this->listing->stok_kg);

        // Status history mencatat transisi
        $this->assertDatabaseHas('order_status_history', [
            'purchase_orders_id_purchase_order' => $po->id_purchase_order,
            'dari' => OrderStatus::MENUNGGU_KONFIRMASI->value,
            'ke' => OrderStatus::DIKONFIRMASI->value,
            'users_id_user' => $this->seller->id_user,
        ]);
    }

    /**
     * AC 5: Konfirmasi ditolak bila stok listing tidak mencukupi volume PO.
     */
    public function test_konfirmasi_po_gagal_bila_stok_listing_tidak_mencukupi(): void
    {
        // Ubah stok listing menjadi hanya 1.000 kg
        $this->listing->stok_kg = 1000.00;
        $this->listing->save();

        $po = PurchaseOrder::factory()->menungguKonfirmasi()->create([
            'volume_kg' => '2000.00', // Butuh 2.000 kg
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
        ]);

        $response = $this->actingAs($this->seller)->post(route('po.confirm', $po));
        $response->assertSessionHasErrors('stok');

        $po->refresh();
        $this->listing->refresh();

        // Status tidak berubah dan stok listing tetap aman
        $this->assertEquals(OrderStatus::MENUNGGU_KONFIRMASI, $po->status);
        $this->assertEquals(1000.00, (float) $this->listing->stok_kg);
    }

    /**
     * AC 6: Seller dapat menolak PO dengan alasan penolakan wajib.
     */
    public function test_seller_dapat_menolak_po_dengan_alasan_wajib(): void
    {
        $po = PurchaseOrder::factory()->menungguKonfirmasi()->create([
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
        ]);

        // Coba tolak tanpa alasan -> gagal validasi
        $responseEmpty = $this->actingAs($this->seller)->post(route('po.reject', $po), [
            'alasan_penolakan' => '',
        ]);
        $responseEmpty->assertSessionHasErrors('alasan_penolakan');

        // Tolak dengan alasan sah
        $response = $this->actingAs($this->seller)->post(route('po.reject', $po), [
            'alasan_penolakan' => 'Jadwal armada pengangkutan tidak cocok dengan tenggat yang diminta.',
        ]);
        $response->assertRedirect(route('po.show', $po));

        $po->refresh();
        $this->assertEquals(OrderStatus::DITOLAK, $po->status);

        $this->assertDatabaseHas('order_status_history', [
            'purchase_orders_id_purchase_order' => $po->id_purchase_order,
            'dari' => OrderStatus::MENUNGGU_KONFIRMASI->value,
            'ke' => OrderStatus::DITOLAK->value,
            'alasan' => 'Jadwal armada pengangkutan tidak cocok dengan tenggat yang diminta.',
            'users_id_user' => $this->seller->id_user,
        ]);
    }

    /**
     * AC 7: Buyer dapat membatalkan PO sebelum dikonfirmasi.
     */
    public function test_buyer_dapat_membatalkan_po_sebelum_dikonfirmasi(): void
    {
        $po = PurchaseOrder::factory()->menungguKonfirmasi()->create([
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
        ]);

        $response = $this->actingAs($this->buyer)->post(route('po.cancel', $po), [
            'alasan' => 'Rencana produksi internal pabrik ditunda.',
        ]);
        $response->assertRedirect(route('po.show', $po));

        $po->refresh();
        $this->assertEquals(OrderStatus::DIBATALKAN, $po->status);

        $this->assertDatabaseHas('order_status_history', [
            'purchase_orders_id_purchase_order' => $po->id_purchase_order,
            'dari' => OrderStatus::MENUNGGU_KONFIRMASI->value,
            'ke' => OrderStatus::DIBATALKAN->value,
            'users_id_user' => $this->buyer->id_user,
        ]);
    }

    /**
     * AC 8: Akses terisolasi, pihak luar dilarang melihat atau mengubah PO (HTTP 403).
     */
    public function test_otorisasi_dan_isolasi_data_po_pihak_luar_ditolak(): void
    {
        $po = PurchaseOrder::factory()->menungguKonfirmasi()->create([
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
        ]);

        // Pengguna luar mencoba melihat detail PO
        $responseView = $this->actingAs($this->otherUser)->get(route('po.show', $po));
        $responseView->assertForbidden();

        // Pengguna luar mencoba mengonfirmasi PO
        $responseConfirm = $this->actingAs($this->otherUser)->post(route('po.confirm', $po));
        $responseConfirm->assertForbidden();

        // Pengguna luar mencoba menolak PO
        $responseReject = $this->actingAs($this->otherUser)->post(route('po.reject', $po), [
            'alasan_penolakan' => 'Aksi ilegal dari pihak luar',
        ]);
        $responseReject->assertForbidden();
    }

    /**
     * UI & Pencarian: Filter No. PO dan nama material.
     */
    public function test_filter_pencarian_dapat_menemukan_no_po_dan_nama_material(): void
    {
        $po = PurchaseOrder::factory()->menungguKonfirmasi()->create([
            'rfq_contracts_id_rfq_contract' => $this->agreedRfq->id_rfq_contract,
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
        ]);

        // Cari berdasarkan formatted code "PO-000X"
        $formatted = $po->formattedNumber();
        $responseCode = $this->actingAs($this->buyer)->get(route('po.index', ['q' => $formatted]));
        $responseCode->assertOk();
        $responseCode->assertSee($formatted);

        // Cari berdasarkan judul material
        $responseTitle = $this->actingAs($this->buyer)->get(route('po.index', ['q' => 'Perca Katun']));
        $responseTitle->assertOk();
        $responseTitle->assertSee($formatted);
    }
}
