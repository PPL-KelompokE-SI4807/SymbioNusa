<?php

namespace Tests\Feature;

use App\Enums\DemandRequestFrequency;
use App\Enums\DemandRequestStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('SYM-18')]
class DemandRequestTest extends TestCase
{
    use DatabaseTransactions;

    protected User $buyer;

    protected User $otherBuyer;

    protected User $seller;

    protected Company $buyerCompany;

    protected Company $otherBuyerCompany;

    protected Company $sellerCompany;

    protected Category $categoryNonB3;

    protected Category $categoryB3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyerCompany = Company::factory()->buyer()->create(['nama' => 'PT Tekstil Sejahtera']);
        $this->otherBuyerCompany = Company::factory()->buyer()->create(['nama' => 'PT Kertas Mandiri']);
        $this->sellerCompany = Company::factory()->seller()->create(['nama' => 'PT Sumber Limbah Industri']);

        $this->buyer = User::factory()->buyer($this->buyerCompany)->create();
        $this->otherBuyer = User::factory()->buyer($this->otherBuyerCompany)->create();
        $this->seller = User::factory()->seller($this->sellerCompany)->create();

        $this->categoryNonB3 = Category::factory()->create([
            'nama' => 'Serat Kain Katun Sisa',
            'is_b3_prohibited' => 0,
            'status' => 'AKTIF',
            'spec_schema' => [
                [
                    'nama' => 'Kadar Air',
                    'tipe' => 'number',
                    'satuan' => '%',
                    'wajib' => true,
                ],
                [
                    'nama' => 'Warna',
                    'tipe' => 'select',
                    'pilihan' => ['Putih', 'Campur'],
                    'wajib' => true,
                ],
                [
                    'nama' => 'Kebersihan',
                    'tipe' => 'text',
                    'wajib' => false,
                ],
            ],
        ]);

        $this->categoryB3 = Category::factory()->create([
            'nama' => 'Limbah Oli Bekas B3',
            'is_b3_prohibited' => 1,
            'status' => 'AKTIF',
        ]);
    }

    /**
     * AC-1 & AC-3: Buyer dapat memublikasikan kebutuhan baru untuk kategori non-B3 yang valid.
     */
    public function test_buyer_can_publish_demand_request_for_valid_non_b3_category(): void
    {
        $payload = [
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 7500.50,
            'frekuensi' => 'BULANAN',
            'harga_maks' => 12500,
            'berlaku_sampai' => now()->addDays(20)->toDateString(),
            'action' => 'publish',
            'spesifikasi_min' => [
                'Kadar Air' => '10',
                'Warna' => 'Putih',
                'Kebersihan' => 'Bebas kotoran',
            ],
        ];

        $response = $this->actingAs($this->buyer)->post(route('buyer.demands.store'), $payload);

        $response->assertRedirect(route('buyer.demands.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('demand_requests', [
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'status' => DemandRequestStatus::TERBUKA->value,
            'frekuensi' => DemandRequestFrequency::BULANAN->value,
            'volume_kg' => '7500.50',
            'harga_maks' => '12500.00',
        ]);
    }

    /**
     * AC-1: Buyer dilarang memublikasikan kebutuhan untuk kategori yang termasuk B3.
     */
    public function test_buyer_cannot_publish_demand_request_for_b3_prohibited_category(): void
    {
        $payload = [
            'categories_id_categories' => $this->categoryB3->id_categories,
            'volume_kg' => 2000,
            'frekuensi' => 'SEKALI',
            'action' => 'publish',
        ];

        $response = $this->actingAs($this->buyer)->post(route('buyer.demands.store'), $payload);

        $response->assertSessionHasErrors('categories_id_categories');
        $this->assertDatabaseMissing('demand_requests', [
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryB3->id_categories,
        ]);
    }

    /**
     * AC-2: Validasi mewajibkan volume > 0 dan field spesifikasi minimal yang bertanda wajib.
     */
    public function test_validation_requires_positive_volume_and_mandatory_spec_schema_fields(): void
    {
        // 1. Volume 0 / negatif
        $response = $this->actingAs($this->buyer)->post(route('buyer.demands.store'), [
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 0,
            'frekuensi' => 'SEKALI',
            'action' => 'publish',
        ]);
        $response->assertSessionHasErrors('volume_kg');

        // 2. Tidak mengisi spesifikasi yang wajib (Kadar Air & Warna)
        $responseSpecs = $this->actingAs($this->buyer)->post(route('buyer.demands.store'), [
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 1000,
            'frekuensi' => 'SEKALI',
            'action' => 'publish',
            'spesifikasi_min' => [
                'Kebersihan' => 'Hanya mengisi opsional',
            ],
        ]);
        $responseSpecs->assertSessionHasErrors(['spesifikasi_min.Kadar Air', 'spesifikasi_min.Warna']);
    }

    /**
     * AC-1 & AC-3: Buyer dapat menyimpan kebutuhan sebagai DRAF tanpa validasi spesifikasi penuh.
     */
    public function test_buyer_can_save_demand_request_as_draft_without_full_specifications(): void
    {
        $payload = [
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 3000,
            'frekuensi' => 'SEKALI',
            'action' => 'draft',
            'spesifikasi_min' => [],
        ];

        $response = $this->actingAs($this->buyer)->post(route('buyer.demands.store'), $payload);

        $response->assertRedirect(route('buyer.demands.index'));
        $this->assertDatabaseHas('demand_requests', [
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'status' => DemandRequestStatus::DRAF->value,
        ]);
    }

    /**
     * AC-4: Buyer dapat melihat daftar kebutuhan perusahaannya (Layar 27) beserta filter dan KPI.
     */
    public function test_buyer_can_view_own_demands_list_and_kpi_stats(): void
    {
        DemandRequest::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'status' => DemandRequestStatus::TERBUKA,
        ]);

        DemandRequest::factory()->draft()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryNonB3->id_categories,
        ]);

        $response = $this->actingAs($this->buyer)->get(route('buyer.demands.index'));

        $response->assertOk();
        $response->assertViewIs('demand.buyer.index');
        $response->assertViewHas('kpi');
        $response->assertSee('Kebutuhan Saya');
        $response->assertSee('Serat Kain Katun Sisa');
    }

    /**
     * AC-4: Buyer dapat mengubah kebutuhan yang berstatus terbuka atau draf.
     */
    public function test_buyer_can_update_own_demand_request(): void
    {
        $demand = DemandRequest::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 1000,
            'status' => DemandRequestStatus::TERBUKA,
        ]);

        $response = $this->actingAs($this->buyer)->put(route('buyer.demands.update', $demand), [
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 2500,
            'frekuensi' => 'MINGGUAN',
            'harga_maks' => 20000,
            'action' => 'publish',
            'spesifikasi_min' => [
                'Kadar Air' => '8',
                'Warna' => 'Putih',
            ],
        ]);

        $response->assertRedirect(route('buyer.demands.index'));
        $demand->refresh();
        $this->assertEquals('2500.00', $demand->volume_kg);
        $this->assertEquals(DemandRequestFrequency::MINGGUAN, $demand->frekuensi);
    }

    /**
     * AC-4: Buyer dapat menutup secara manual kebutuhan yang berstatus terbuka.
     */
    public function test_buyer_can_close_open_demand_request(): void
    {
        $demand = DemandRequest::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'status' => DemandRequestStatus::TERBUKA,
        ]);

        $response = $this->actingAs($this->buyer)->patch(route('buyer.demands.close', $demand));

        $response->assertRedirect(route('buyer.demands.index'));
        $demand->refresh();
        $this->assertEquals(DemandRequestStatus::KEDALUWARSA, $demand->status);
    }

    /**
     * AC-4: Buyer dapat menghapus draf kebutuhan miliknya.
     */
    public function test_buyer_can_delete_draft_demand_request(): void
    {
        $draft = DemandRequest::factory()->draft()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        $response = $this->actingAs($this->buyer)->delete(route('buyer.demands.destroy', $draft));

        $response->assertRedirect(route('buyer.demands.index'));
        $this->assertDatabaseMissing('demand_requests', [
            'id_demand_request' => $draft->id_demand_request,
        ]);
    }

    /**
     * AC-5: Seller dapat mengakses Papan Kebutuhan (Layar 28) dan menyaring data.
     */
    public function test_seller_can_browse_open_demands_board_and_filter_by_category_and_volume(): void
    {
        DemandRequest::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 15000,
            'status' => DemandRequestStatus::TERBUKA,
        ]);

        $response = $this->actingAs($this->seller)->get(route('seller.demands.index', [
            'category' => $this->categoryNonB3->id_categories,
            'vol_min' => 10000,
        ]));

        $response->assertOk();
        $response->assertViewIs('demand.seller.index');
        $response->assertSee('PT Tekstil Sejahtera');
        $response->assertSee('15.000,00 kg');
    }

    /**
     * AC-5: Seller dapat melihat rincian detail spesifikasi kebutuhan terbuka.
     */
    public function test_seller_can_view_open_demand_details(): void
    {
        $demand = DemandRequest::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'status' => DemandRequestStatus::TERBUKA,
            'spesifikasi_min' => [
                'Kadar Air' => '9%',
                'Warna' => 'Putih Murni',
            ],
        ]);

        $response = $this->actingAs($this->seller)->get(route('seller.demands.show', $demand));

        $response->assertOk();
        $response->assertViewIs('demand.seller.show');
        $response->assertSee('PT Tekstil Sejahtera');
        $response->assertSee('Kadar Air');
        $response->assertSee('9%');
    }

    /**
     * AC-5 & AC-7: Seller tidak boleh melihat kebutuhan yang masih berstatus DRAF.
     */
    public function test_seller_cannot_view_draft_demands_of_buyer(): void
    {
        $draft = DemandRequest::factory()->draft()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        $response = $this->actingAs($this->seller)->get(route('seller.demands.show', $draft));

        $response->assertForbidden();
    }

    /**
     * AC-6: Command artisan demand:close-expired menutup kebutuhan yang tanggal berlakunya telah lampau.
     */
    public function test_scheduled_command_closes_expired_demand_requests(): void
    {
        $expiredDemand = DemandRequest::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'status' => DemandRequestStatus::TERBUKA,
            'berlaku_sampai' => now()->subDay()->toDateString(),
        ]);

        $stillActiveDemand = DemandRequest::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'status' => DemandRequestStatus::TERBUKA,
            'berlaku_sampai' => now()->addDays(5)->toDateString(),
        ]);

        $this->artisan('demand:close-expired')
            ->expectsOutputToContain('Berhasil menutup 1 kebutuhan kedaluwarsa.')
            ->assertSuccessful();

        $expiredDemand->refresh();
        $stillActiveDemand->refresh();

        $this->assertEquals(DemandRequestStatus::KEDALUWARSA, $expiredDemand->status);
        $this->assertEquals(DemandRequestStatus::TERBUKA, $stillActiveDemand->status);
    }

    /**
     * AC-7: Pembatasan akses lintas perusahaan dan otorisasi RBAC (403 Forbidden).
     */
    public function test_cross_company_isolation_and_rbac_prevent_unauthorized_modifications(): void
    {
        $demand = DemandRequest::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'status' => DemandRequestStatus::TERBUKA,
        ]);

        // Buyer lain mencoba mengedit
        $responseEdit = $this->actingAs($this->otherBuyer)->put(route('buyer.demands.update', $demand), [
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 500,
            'frekuensi' => 'SEKALI',
            'action' => 'publish',
        ]);
        $responseEdit->assertForbidden();

        // Buyer lain mencoba menutup
        $responseClose = $this->actingAs($this->otherBuyer)->patch(route('buyer.demands.close', $demand));
        $responseClose->assertForbidden();

        // Seller mencoba membuat permintaan kebutuhan
        $responseSellerCreate = $this->actingAs($this->seller)->post(route('buyer.demands.store'), [
            'categories_id_categories' => $this->categoryNonB3->id_categories,
            'volume_kg' => 500,
            'frekuensi' => 'SEKALI',
        ]);
        $responseSellerCreate->assertForbidden();
    }
}
