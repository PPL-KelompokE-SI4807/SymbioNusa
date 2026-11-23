<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('SYM-13')]
class ListingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_seller_can_create_listing_as_draft(): void
    {
        $seller = User::factory()->seller()->create();
        $category = Category::factory()->create([
            'is_b3_prohibited' => 0,
            'status' => 'AKTIF',
        ]);

        $response = $this->actingAs($seller)->post(route('seller.listings.store'), [
            'judul' => 'Sisa Kain Perca Katun Draf',
            'categories_id_categories' => $category->id_categories,
            'volume_kg' => 2500,
            'harga_per_kg' => 4000,
            'lokasi' => 'Bandung, Jawa Barat',
            'status' => 'DRAF',
            'metode_buang_sebelumnya' => 'TPA',
            'biaya_buang_per_kg' => 300,
            'spesifikasi' => ['Kadar Air' => '5%'],
        ]);

        $response->assertRedirect(route('seller.listings.index'));
        $this->assertDatabaseHas('listings', [
            'judul' => 'Sisa Kain Perca Katun Draf',
            'companies_id_company' => $seller->id_company,
            'categories_id_categories' => $category->id_categories,
            'status' => 'DRAF',
            'volume_kg' => 2500,
            'stok_kg' => 2500,
        ]);
    }

    public function test_seller_can_create_listing_as_published_with_attachments(): void
    {
        $seller = User::factory()->seller()->create();
        $category = Category::factory()->create([
            'is_b3_prohibited' => 0,
            'wajib_sertifikat_lab' => 0,
            'status' => 'AKTIF',
        ]);

        $surat = UploadedFile::fake()->create('surat_non_b3.pdf', 200, 'application/pdf');
        $foto = UploadedFile::fake()->image('sampel.jpg');

        $response = $this->actingAs($seller)->post(route('seller.listings.store'), [
            'judul' => 'Serbuk Kayu Jati Siap Angkut',
            'categories_id_categories' => $category->id_categories,
            'volume_kg' => 10000,
            'harga_per_kg' => 1500,
            'lokasi' => 'Cimahi, Jawa Barat',
            'status' => 'PUBLISHED',
            'surat_non_b3' => $surat,
            'foto_sampel' => $foto,
            'tanggal_pengambilan_sampel' => '2026-11-10',
            'spesifikasi' => ['Kadar Air' => '8', 'Tingkat Kebersihan' => 'Bersih'],
        ]);

        $response->assertRedirect(route('seller.listings.index'));

        /** @var Listing $listing */
        $listing = Listing::where('judul', 'Serbuk Kayu Jati Siap Angkut')->firstOrFail();
        $this->assertEquals('PUBLISHED', $listing->status);

        $this->assertDatabaseHas('listing_attachments', [
            'listings_id_listings' => $listing->id_listings,
            'jenis' => 'SURAT_NON_B3',
        ]);

        $this->assertDatabaseHas('listing_attachments', [
            'listings_id_listings' => $listing->id_listings,
            'jenis' => 'FOTO_SAMPEL',
        ]);

        // Tanggal pengambilan sampel yang diisi seller tersimpan apa adanya.
        $foto = $listing->attachments()->where('jenis', 'FOTO_SAMPEL')->firstOrFail();
        $this->assertSame('2026-11-10', $foto->tanggal_pengambilan?->toDateString());
    }

    public function test_listing_creation_fails_if_category_is_b3_prohibited(): void
    {
        $seller = User::factory()->seller()->create();
        $b3Category = Category::factory()->b3()->create([
            'nama' => 'Limbah Oli Bekas B3',
            'is_b3_prohibited' => 1,
            'status' => 'AKTIF',
        ]);

        $response = $this->actingAs($seller)->post(route('seller.listings.store'), [
            'judul' => 'Penawaran Oli Bekas Drum',
            'categories_id_categories' => $b3Category->id_categories,
            'volume_kg' => 1000,
            'harga_per_kg' => 2000,
            'lokasi' => 'Surabaya',
            'status' => 'PUBLISHED',
            'surat_non_b3' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('categories_id_categories');
        $this->assertDatabaseMissing('listings', [
            'judul' => 'Penawaran Oli Bekas Drum',
        ]);
    }

    public function test_published_listing_requires_lab_certificate_if_mandated_by_category(): void
    {
        $seller = User::factory()->seller()->create();
        $labCategory = Category::factory()->create([
            'is_b3_prohibited' => 0,
            'wajib_sertifikat_lab' => 1,
            'status' => 'AKTIF',
        ]);

        // Coba publikasikan tanpa sertifikat lab
        $response = $this->actingAs($seller)->post(route('seller.listings.store'), [
            'judul' => 'Slag Baja Non-B3 Bersertifikat',
            'categories_id_categories' => $labCategory->id_categories,
            'volume_kg' => 8000,
            'harga_per_kg' => 1200,
            'lokasi' => 'Cilegon',
            'status' => 'PUBLISHED',
            'surat_non_b3' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('sertifikat_lab');

        // Publikasikan dengan sertifikat lab
        $responseSuccess = $this->actingAs($seller)->post(route('seller.listings.store'), [
            'judul' => 'Slag Baja Non-B3 Bersertifikat',
            'categories_id_categories' => $labCategory->id_categories,
            'volume_kg' => 8000,
            'harga_per_kg' => 1200,
            'lokasi' => 'Cilegon',
            'status' => 'PUBLISHED',
            'surat_non_b3' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'sertifikat_lab' => UploadedFile::fake()->create('lab.pdf', 100, 'application/pdf'),
            'spesifikasi' => ['Kadar Air' => '3', 'Tingkat Kebersihan' => 'Bersih'],
        ]);

        $responseSuccess->assertRedirect(route('seller.listings.index'));
        $this->assertDatabaseHas('listings', [
            'judul' => 'Slag Baja Non-B3 Bersertifikat',
            'status' => 'PUBLISHED',
        ]);
    }

    public function test_seller_cannot_update_other_company_listing(): void
    {
        $seller1 = User::factory()->seller()->create();
        $seller2 = User::factory()->seller()->create();

        $listing = Listing::factory()->create([
            'companies_id_company' => $seller1->id_company,
            'judul' => 'Listing Asli Perusahaan 1',
        ]);

        $response = $this->actingAs($seller2)->put(route('seller.listings.update', $listing), [
            'judul' => 'Upaya Pembajakan Judul',
            'categories_id_categories' => $listing->categories_id_categories,
            'volume_kg' => 1000,
            'stok_kg' => 1000,
            'harga_per_kg' => 2000,
            'lokasi' => 'Jakarta',
            'status' => 'PUBLISHED',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('listings', [
            'judul' => 'Upaya Pembajakan Judul',
        ]);
    }

    public function test_seller_can_archive_own_listing(): void
    {
        $seller = User::factory()->seller()->create();
        $listing = Listing::factory()->create([
            'companies_id_company' => $seller->id_company,
            'status' => 'PUBLISHED',
        ]);

        $response = $this->actingAs($seller)->post(route('seller.listings.archive', $listing));

        $response->assertRedirect(route('seller.listings.index'));
        $this->assertEquals('DIARSIPKAN', $listing->fresh()?->status);
    }

    public function test_non_seller_cannot_create_or_manage_listing(): void
    {
        $buyer = User::factory()->buyer()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($buyer)->get(route('seller.listings.index'))->assertForbidden();
        $this->actingAs($buyer)->get(route('seller.listings.create'))->assertForbidden();
        $this->actingAs($admin)->post(route('seller.listings.store'), [])->assertForbidden();
    }

    public function test_unverified_seller_cannot_create_listing(): void
    {
        $unverifiedCompany = Company::factory()->seller()->pending()->create();
        $unverifiedSeller = User::factory()->seller($unverifiedCompany)->create();

        $this->actingAs($unverifiedSeller)->get(route('seller.listings.create'))->assertForbidden();
    }

    public function test_catalog_displays_only_published_listings_with_stock(): void
    {
        $buyer = User::factory()->buyer()->create();

        $publishedListing = Listing::factory()->create([
            'judul' => 'Material Ready Stok',
            'status' => 'PUBLISHED',
            'stok_kg' => 500,
        ]);

        $draftListing = Listing::factory()->draf()->create([
            'judul' => 'Material Masih Draf',
        ]);

        $outOfStockListing = Listing::factory()->outOfStock()->create([
            'judul' => 'Material Habis',
            'status' => 'PUBLISHED',
        ]);

        $response = $this->actingAs($buyer)->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('Material Ready Stok');
        $response->assertDontSee('Material Masih Draf');
        $response->assertDontSee('Material Habis');
    }

    public function test_catalog_can_be_filtered_by_category_and_search_query(): void
    {
        $buyer = User::factory()->buyer()->create();
        $cat1 = Category::factory()->create(['nama' => 'Plastik Daur Ulang']);
        $cat2 = Category::factory()->create(['nama' => 'Kertas Kardus']);

        Listing::factory()->create([
            'judul' => 'Cacahan Plastik HDPE',
            'categories_id_categories' => $cat1->id_categories,
            'status' => 'PUBLISHED',
            'stok_kg' => 1000,
            'lokasi' => 'Semarang',
        ]);

        Listing::factory()->create([
            'judul' => 'Limbah Kardus Box Corrugated',
            'categories_id_categories' => $cat2->id_categories,
            'status' => 'PUBLISHED',
            'stok_kg' => 2000,
            'lokasi' => 'Bandung',
        ]);

        // Filter kategori
        $resCat = $this->actingAs($buyer)->get(route('catalog.index', ['kategori' => $cat1->id_categories]));
        $resCat->assertOk();
        $resCat->assertSee('Cacahan Plastik HDPE');
        $resCat->assertDontSee('Limbah Kardus Box Corrugated');

        // Filter search keyword
        $resSearch = $this->actingAs($buyer)->get(route('catalog.index', ['q' => 'Bandung']));
        $resSearch->assertOk();
        $resSearch->assertSee('Limbah Kardus Box Corrugated');
        $resSearch->assertDontSee('Cacahan Plastik HDPE');
    }
}
