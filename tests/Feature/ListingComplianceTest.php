<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\Listing;
use App\Models\ListingAttachment;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * PBI-09 (SYM-13): kelengkapan kriteria listing — lampiran, stok, spesifikasi,
 * kunci inspeksi, moderasi Admin, dan katalog hanya untuk mitra terverifikasi.
 */
#[Group('SYM-13')]
class ListingComplianceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * @return array<string, mixed>
     */
    private function updatePayload(Listing $listing, array $overrides = []): array
    {
        return array_merge([
            'judul' => $listing->judul,
            'categories_id_categories' => $listing->categories_id_categories,
            'volume_kg' => 5000,
            'stok_kg' => 5000,
            'harga_per_kg' => 3500,
            'lokasi' => 'Bandung',
            'status' => 'DRAF',
        ], $overrides);
    }

    private function sellerWithListing(): array
    {
        $seller = User::factory()->seller()->create();
        $listing = Listing::factory()->draf()->create(['companies_id_company' => $seller->id_company]);

        return [$seller, $listing];
    }

    public function test_mengganti_lampiran_menonaktifkan_lampiran_lama_dengan_jenis_sama(): void
    {
        [$seller, $listing] = $this->sellerWithListing();
        $old = ListingAttachment::factory()->suratNonB3()->create(['listings_id_listings' => $listing->id_listings]);

        $this->actingAs($seller)
            ->put(route('seller.listings.update', $listing), $this->updatePayload($listing, [
                'surat_non_b3' => UploadedFile::fake()->create('surat-baru.pdf', 100, 'application/pdf'),
            ]))
            ->assertRedirect(route('seller.listings.index'));

        $this->assertSame('NONAKTIF', $old->fresh()->status);
        $this->assertSame(1, $listing->activeAttachments()->where('jenis', 'SURAT_NON_B3')->count());
    }

    public function test_stok_tidak_boleh_melebihi_volume(): void
    {
        [$seller, $listing] = $this->sellerWithListing();

        $this->actingAs($seller)
            ->put(route('seller.listings.update', $listing), $this->updatePayload($listing, ['stok_kg' => 6000]))
            ->assertSessionHasErrors('stok_kg');
    }

    public function test_spesifikasi_wajib_templat_diperiksa_server_saat_publikasi(): void
    {
        $seller = User::factory()->seller()->create();
        $category = Category::factory()->create(['is_b3_prohibited' => 0, 'wajib_sertifikat_lab' => 0, 'status' => 'AKTIF']);

        $this->actingAs($seller)
            ->post(route('seller.listings.store'), [
                'judul' => 'Listing Tanpa Spesifikasi',
                'categories_id_categories' => $category->id_categories,
                'volume_kg' => 100,
                'harga_per_kg' => 1000,
                'lokasi' => 'Bandung',
                'status' => 'PUBLISHED',
                'surat_non_b3' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
                'spesifikasi' => ['Kadar Air' => '10'],
            ])
            ->assertSessionHasErrors('spesifikasi');

        $this->assertDatabaseMissing('listings', ['judul' => 'Listing Tanpa Spesifikasi']);
    }

    public function test_lampiran_tidak_dapat_diganti_selama_inspeksi(): void
    {
        [$seller, $listing] = $this->sellerWithListing();
        $rfq = RfqContract::factory()->create(['listings_id_listings' => $listing->id_listings]);
        PurchaseOrder::factory()->create([
            'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'companies_id_seller' => $seller->id_company,
            'status' => OrderStatus::INSPEKSI,
        ]);

        $this->actingAs($seller)
            ->put(route('seller.listings.update', $listing), $this->updatePayload($listing, [
                'foto_sampel' => UploadedFile::fake()->image('baru.jpg'),
            ]))
            ->assertSessionHasErrors('surat_non_b3');

        $this->assertSame(0, $listing->attachments()->count());

        $this->actingAs($seller)
            ->get(route('seller.listings.edit', $listing))
            ->assertOk()
            ->assertSee('sedang dalam proses inspeksi');
    }

    public function test_portal_seller_tidak_menampilkan_listing_perusahaan_lain(): void
    {
        $other = Listing::factory()->create(['status' => 'PUBLISHED']);
        $seller = User::factory()->seller()->create();

        $this->actingAs($seller)->get(route('seller.listings.show', $other))->assertForbidden();
    }

    public function test_admin_dapat_memoderasi_dan_menurunkan_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $listing = Listing::factory()->create(['judul' => 'Listing Untuk Dimoderasi', 'status' => 'PUBLISHED']);

        $this->actingAs($admin)
            ->get(route('admin.listings.index', ['q' => 'Dimoderasi']))
            ->assertOk()
            ->assertSee('Listing Untuk Dimoderasi');

        $this->actingAs($admin)
            ->post(route('admin.listings.archive', $listing))
            ->assertRedirect();

        $this->assertSame('DIARSIPKAN', $listing->fresh()->status);
    }

    public function test_non_admin_tidak_dapat_memoderasi_listing(): void
    {
        $seller = User::factory()->seller()->create();
        $listing = Listing::factory()->create(['status' => 'PUBLISHED']);

        $this->actingAs($seller)->get(route('admin.listings.index'))->assertForbidden();
        $this->actingAs($seller)->post(route('admin.listings.archive', $listing))->assertForbidden();
        $this->assertSame('PUBLISHED', $listing->fresh()->status);
    }

    public function test_katalog_menyembunyikan_listing_perusahaan_belum_terverifikasi(): void
    {
        $buyer = User::factory()->buyer()->create();
        $pending = Company::factory()->seller()->pending()->create();
        $listing = Listing::factory()->create([
            'judul' => 'Listing Mitra Tinjauan Ulang',
            'status' => 'PUBLISHED',
            'companies_id_company' => $pending->id_company,
        ]);

        $this->actingAs($buyer)->get(route('catalog.index'))->assertOk()->assertDontSee('Listing Mitra Tinjauan Ulang');
        $this->actingAs($buyer)->get(route('catalog.show', $listing))->assertNotFound();
    }

    public function test_katalog_hanya_menampilkan_lampiran_aktif_tanpa_label_terverifikasi(): void
    {
        $buyer = User::factory()->buyer()->create();
        $listing = Listing::factory()->create(['status' => 'PUBLISHED']);
        ListingAttachment::factory()->suratNonB3()->create(['listings_id_listings' => $listing->id_listings, 'status' => 'NONAKTIF', 'file_url' => 'listings/lama.pdf']);
        ListingAttachment::factory()->suratNonB3()->create(['listings_id_listings' => $listing->id_listings, 'file_url' => 'listings/baru.pdf']);

        $this->actingAs($buyer)
            ->get(route('catalog.show', $listing))
            ->assertOk()
            ->assertSee('listings/baru.pdf')
            ->assertDontSee('listings/lama.pdf')
            ->assertDontSee('Terverifikasi');
    }
}
