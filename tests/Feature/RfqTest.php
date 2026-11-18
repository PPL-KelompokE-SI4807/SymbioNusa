<?php

namespace Tests\Feature;

use App\Enums\RfqStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\Listing;
use App\Models\RfqContract;
use App\Models\RfqOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('SYM-8')]
class RfqTest extends TestCase
{
    use DatabaseTransactions;

    protected User $seller;

    protected User $buyer;

    protected Company $sellerCompany;

    protected Company $buyerCompany;

    protected Listing $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerCompany = Company::factory()->seller()->create();
        $this->buyerCompany = Company::factory()->buyer()->create();

        $this->seller = User::factory()->seller($this->sellerCompany)->create();
        $this->buyer = User::factory()->buyer($this->buyerCompany)->create();

        $category = Category::factory()->create(['status' => 'AKTIF', 'is_b3_prohibited' => 0]);

        $this->listing = Listing::factory()->create([
            'companies_id_company' => $this->sellerCompany->id_company,
            'categories_id_categories' => $category->id_categories,
            'status' => 'PUBLISHED',
            'volume_kg' => 5000.00,
            'stok_kg' => 5000.00,
            'harga_per_kg' => 3500.00,
        ]);
    }

    public function test_buyer_dapat_melihat_halaman_daftar_dan_form_rfq(): void
    {
        $response = $this->actingAs($this->buyer)->get(route('rfq.index'));
        $response->assertOk();
        $response->assertSee('RFQ &amp; Negosiasi', false);

        $responseCreate = $this->actingAs($this->buyer)->get(route('rfq.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee('Ajukan RFQ Baru');
    }

    public function test_filter_pencarian_dapat_menemukan_no_rfq_berformat_dan_material(): void
    {
        $rfq = RfqContract::factory()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
            'status' => RfqStatus::TERBUKA,
        ]);

        $formattedNo = 'RFQ-'.str_pad((string) $rfq->id_rfq_contract, 4, '0', STR_PAD_LEFT);

        // Cari dengan format RFQ-0001
        $resByNo = $this->actingAs($this->buyer)->get(route('rfq.index', ['q' => $formattedNo]));
        $resByNo->assertOk();
        $resByNo->assertSee($formattedNo);

        // Cari dengan hanya ID angka
        $resById = $this->actingAs($this->buyer)->get(route('rfq.index', ['q' => (string) $rfq->id_rfq_contract]));
        $resById->assertOk();
        $resById->assertSee($formattedNo);

        // Cari dengan nama material
        $resByName = $this->actingAs($this->buyer)->get(route('rfq.index', ['q' => substr($this->listing->judul, 0, 5)]));
        $resByName->assertOk();
        $resByName->assertSee($formattedNo);
    }

    public function test_buyer_dapat_mengajukan_rfq_jalur_normal(): void
    {
        $payload = [
            'listings_id_listings' => $this->listing->id_listings,
            'volume_kg' => 2000.00,
            'harga_per_kg' => 3200.00,
            'tenggat_hari' => 3,
            'catatan' => 'Mohon pertimbangan harga penawaran awal kami.',
        ];

        $response = $this->actingAs($this->buyer)->post(route('rfq.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('rfq_contracts', [
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
            'status' => 'TERBUKA',
        ]);

        $rfq = RfqContract::where('listings_id_listings', $this->listing->id_listings)->latest('id_rfq_contract')->first();
        $this->assertNotNull($rfq);

        $this->assertDatabaseHas('rfq_offers', [
            'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'companies_id_company' => $this->buyerCompany->id_company,
            'volume_kg' => 2000.00,
            'harga_per_kg' => 3200.00,
            'urutan' => 1,
        ]);
    }

    public function test_pengajuan_rfq_ditolak_jika_volume_melebihi_stok(): void
    {
        $payload = [
            'listings_id_listings' => $this->listing->id_listings,
            'volume_kg' => 6000.00, // Stok listing hanya 5000 kg
            'harga_per_kg' => 3000.00,
        ];

        $response = $this->actingAs($this->buyer)->post(route('rfq.store'), $payload);

        $response->assertSessionHasErrors('volume_kg');
        $this->assertDatabaseMissing('rfq_contracts', [
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);
    }

    public function test_pengajuan_rfq_ditolak_jika_listing_milik_perusahaan_sendiri(): void
    {
        // Seller mencoba mengajukan RFQ pada listing perusahaannya sendiri
        $payload = [
            'listings_id_listings' => $this->listing->id_listings,
            'volume_kg' => 1000.00,
            'harga_per_kg' => 3000.00,
        ];

        $response = $this->actingAs($this->seller)->post(route('rfq.store'), $payload);

        // Harus gagal (bisa 403 karena role bukan BUYER atau validasi self-company)
        $this->assertTrue(
            $response->status() === 403 || session()->has('errors')
        );
    }

    public function test_seller_dapat_mengajukan_penawaran_balik_counter_offer(): void
    {
        $rfq = RfqContract::factory()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
            'status' => RfqStatus::TERBUKA,
        ]);

        RfqOffer::factory()->create([
            'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'companies_id_company' => $this->buyerCompany->id_company,
            'volume_kg' => 2000.00,
            'harga_per_kg' => 3000.00,
            'urutan' => 1,
        ]);

        $counterPayload = [
            'volume_kg' => 2000.00,
            'harga_per_kg' => 3300.00,
            'catatan' => 'Kualitas sangat baik, kami tawarkan Rp 3.300/kg.',
        ];

        $response = $this->actingAs($this->seller)->post(route('rfq.counter', $rfq), $counterPayload);

        $response->assertRedirect(route('rfq.show', $rfq));

        $rfq->refresh();
        $this->assertEquals(RfqStatus::NEGOSIASI, $rfq->status);

        $this->assertDatabaseHas('rfq_offers', [
            'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'companies_id_company' => $this->sellerCompany->id_company,
            'volume_kg' => 2000.00,
            'harga_per_kg' => 3300.00,
            'urutan' => 2,
        ]);
    }

    public function test_penawaran_balik_ditolak_jika_mencapai_batas_maksimal_5_putaran(): void
    {
        $rfq = RfqContract::factory()->negosiasi()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        // Buat tawaran sampai putaran ke-5
        for ($i = 1; $i <= 5; $i++) {
            RfqOffer::factory()->create([
                'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
                'companies_id_company' => $i % 2 === 1 ? $this->buyerCompany->id_company : $this->sellerCompany->id_company,
                'volume_kg' => 1000.00,
                'harga_per_kg' => 3000.00 + ($i * 50),
                'urutan' => $i,
            ]);
        }

        // Putaran ke-5 dibuat oleh buyer, seller mencoba putaran ke-6
        $response = $this->actingAs($this->seller)->post(route('rfq.counter', $rfq), [
            'volume_kg' => 1000.00,
            'harga_per_kg' => 3350.00,
        ]);

        // Harus gagal (403 atau error batas putaran)
        $this->assertTrue(
            $response->status() === 403 || session()->has('errors')
        );
        $this->assertDatabaseMissing('rfq_offers', [
            'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'urutan' => 6,
        ]);
    }

    public function test_pihak_lawan_dapat_menerima_tawaran_dan_mengunci_kesepakatan(): void
    {
        $rfq = RfqContract::factory()->terbuka()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        RfqOffer::factory()->create([
            'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'companies_id_company' => $this->buyerCompany->id_company,
            'volume_kg' => 1800.00,
            'harga_per_kg' => 3400.00,
            'urutan' => 1,
        ]);

        // Seller menerima tawaran Buyer
        $response = $this->actingAs($this->seller)->post(route('rfq.accept', $rfq));

        $response->assertRedirect(route('rfq.show', $rfq));

        $rfq->refresh();
        $this->assertEquals(RfqStatus::DISEPAKATI, $rfq->status);
        $this->assertEquals(1800.00, (float) $rfq->volume_kg_disepakati);
        $this->assertEquals(3400.00, (float) $rfq->harga_disepakati);
    }

    public function test_pihak_lawan_dapat_menolak_tawaran_rfq(): void
    {
        $rfq = RfqContract::factory()->terbuka()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        RfqOffer::factory()->create([
            'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'companies_id_company' => $this->buyerCompany->id_company,
            'volume_kg' => 1000.00,
            'harga_per_kg' => 2000.00,
            'urutan' => 1,
        ]);

        $response = $this->actingAs($this->seller)->post(route('rfq.reject', $rfq), [
            'alasan_penolakan' => 'Harga yang ditawarkan terlalu jauh di bawah batas biaya produksi kami.',
        ]);

        $response->assertRedirect(route('rfq.show', $rfq));

        $rfq->refresh();
        $this->assertEquals(RfqStatus::DITOLAK, $rfq->status);
    }

    public function test_buyer_dapat_menarik_pengajuan_rfq_miliknya(): void
    {
        $rfq = RfqContract::factory()->terbuka()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        RfqOffer::factory()->create([
            'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'companies_id_company' => $this->buyerCompany->id_company,
            'volume_kg' => 1000.00,
            'harga_per_kg' => 3000.00,
            'urutan' => 1,
        ]);

        $response = $this->actingAs($this->buyer)->post(route('rfq.withdraw', $rfq));

        $response->assertRedirect(route('rfq.show', $rfq));

        $rfq->refresh();
        $this->assertEquals(RfqStatus::DITARIK, $rfq->status);
    }

    public function test_peran_lain_dan_perusahaan_luar_tidak_dapat_mengakses_rfq(): void
    {
        $rfq = RfqContract::factory()->terbuka()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        // Inspector dilarang mengakses
        $inspector = User::factory()->inspector()->create();
        $this->actingAs($inspector)->get(route('rfq.show', $rfq))->assertForbidden();

        // Buyer dari perusahaan lain dilarang mengakses
        $otherBuyer = User::factory()->buyer()->create();
        $this->actingAs($otherBuyer)->get(route('rfq.show', $rfq))->assertForbidden();
    }

    public function test_artisan_command_rfq_expire_dapat_menandai_rfq_lewat_tenggat(): void
    {
        $expiredRfq = RfqContract::factory()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
            'status' => RfqStatus::TERBUKA,
            'tenggat' => now()->subDay(),
        ]);

        $activeRfq = RfqContract::factory()->create([
            'listings_id_listings' => $this->listing->id_listings,
            'companies_id_company' => $this->buyerCompany->id_company,
            'status' => RfqStatus::TERBUKA,
            'tenggat' => now()->addDays(2),
        ]);

        $this->artisan('rfq:expire')
            ->expectsOutputToContain('Berhasil memperbarui')
            ->assertExitCode(0);

        $expiredRfq->refresh();
        $activeRfq->refresh();

        $this->assertEquals(RfqStatus::KEDALUWARSA, $expiredRfq->status);
        $this->assertEquals(RfqStatus::TERBUKA, $activeRfq->status);
    }
}
