<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Listing;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('SYM-6')]
class CategoryManagementTest extends TestCase
{
    protected User $adminUser;

    protected User $sellerUser;

    protected User $buyerUser;

    protected User $inspectorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDatabaseTablesExist();

        // Create test users
        $this->adminUser = User::create([
            'nama' => 'Admin Test DLH',
            'email' => 'admin@symbionusa.test',
            'role' => 'ADMIN',
            'password_hash' => bcrypt('password'),
            'status' => 'AKTIF',
        ]);

        $company = Company::create([
            'nama' => 'PT Mitra Industri Non-B3',
            'tipe' => 'SELLER',
            'status_verifikasi' => 'TERVERIFIKASI',
        ]);

        $this->sellerUser = User::create([
            'nama' => 'Budi Seller',
            'email' => 'seller@symbionusa.test',
            'role' => 'SELLER',
            'password_hash' => bcrypt('password'),
            'status' => 'AKTIF',
            'id_company' => $company->id_company,
        ]);

        $this->buyerUser = User::create([
            'nama' => 'Siti Buyer',
            'email' => 'buyer@symbionusa.test',
            'role' => 'BUYER',
            'password_hash' => bcrypt('password'),
            'status' => 'AKTIF',
            'id_company' => $company->id_company,
        ]);

        $this->inspectorUser = User::create([
            'nama' => 'Ir. Joko Inspector',
            'email' => 'inspector@symbionusa.test',
            'role' => 'INSPECTOR',
            'password_hash' => bcrypt('password'),
            'status' => 'AKTIF',
        ]);
    }

    protected function ensureDatabaseTablesExist(): void
    {
        // Setup in-memory SQLite schema compatible with DDL
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        if (! Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->increments('id_company');
                $table->string('nama', 255);
                $table->string('tipe', 50)->default('SELLER');
                $table->string('nib', 45)->nullable();
                $table->string('alamat', 255)->nullable();
                $table->string('kontak', 45)->nullable();
                $table->double('lat')->nullable();
                $table->double('lng')->nullable();
                $table->string('tos_versi', 45)->nullable();
                $table->timestamp('tos_disetujui_pada')->nullable();
                $table->string('status_verifikasi', 50)->default('PENDING');
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id_user');
                $table->string('nama', 255);
                $table->string('email', 255)->unique();
                $table->string('role', 50);
                $table->string('password_hash', 255)->nullable();
                $table->string('status', 50)->default('AKTIF');
                $table->integer('id_company')->nullable();
            });
        }

        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('id_categories');
                $table->string('nama', 100);
                $table->json('spec_schema')->nullable();
                $table->tinyInteger('is_b3_prohibited')->default(0);
                $table->decimal('toleransi_susut_persen', 5, 2)->default(0);
                $table->decimal('kadar_air_maks_persen', 5, 2)->nullable();
                $table->decimal('toleransi_kadar_air_poin', 5, 2)->nullable();
                $table->string('tingkat_risiko', 50)->default('RENDAH');
                $table->tinyInteger('wajib_sertifikat_lab')->default(0);
                $table->integer('categories_id_categories')->nullable();
                $table->string('status', 50)->default('AKTIF');
            });
        }

        if (! Schema::hasTable('listings')) {
            Schema::create('listings', function (Blueprint $table) {
                $table->increments('id_listings');
                $table->string('judul', 255);
                $table->json('spesifikasi')->nullable();
                $table->decimal('volume_kg', 12, 2)->default(0);
                $table->decimal('stok_kg', 12, 2)->default(0);
                $table->decimal('harga_per_kg', 12, 2)->default(0);
                $table->decimal('biaya_buang_per_kg', 12, 2)->nullable();
                $table->string('metode_buang_sebelumnya', 100)->nullable();
                $table->string('lokasi', 255)->nullable();
                $table->string('status', 50)->default('DRAF');
                $table->integer('companies_id_company')->default(1);
                $table->integer('categories_id_categories')->default(1);
            });
        }

        if (! Schema::hasTable('demand_requests')) {
            Schema::create('demand_requests', function (Blueprint $table) {
                $table->increments('id_demand_request');
                $table->json('spesifikasi_min')->nullable();
                $table->decimal('volume_kg', 12, 2)->default(0);
                $table->decimal('harga_maks', 12, 2)->nullable();
                $table->date('berlaku_sampai')->nullable();
                $table->string('status', 50)->default('TERBUKA');
                $table->integer('companies_id_company')->default(1);
                $table->integer('categories_id_categories')->default(1);
                $table->string('frekuensi', 50)->default('SEKALI');
            });
        }
    }

    public function test_guest_cannot_access_categories(): void
    {
        $response = $this->get('/admin/kategori');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_categories(): void
    {
        // Seller cannot access
        $this->actingAs($this->sellerUser)
            ->get('/admin/kategori')
            ->assertStatus(403);

        // Buyer cannot access
        $this->actingAs($this->buyerUser)
            ->get('/admin/kategori')
            ->assertStatus(403);

        // Inspector cannot access
        $this->actingAs($this->inspectorUser)
            ->get('/admin/kategori')
            ->assertStatus(403);
    }

    public function test_admin_can_view_category_index_and_hierarchy(): void
    {
        $parent = Category::create([
            'nama' => 'Plastik Polimer',
            'toleransi_susut_persen' => 2.50,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'AKTIF',
        ]);

        $child = Category::create([
            'nama' => 'PET Flakes Bening',
            'categories_id_categories' => $parent->id_categories,
            'toleransi_susut_persen' => 1.50,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'AKTIF',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/kategori');

        $response->assertStatus(200);
        $response->assertSee('Plastik Polimer');
        $response->assertSee('PET Flakes Bening');
        $response->assertSee('Master Kategori');
    }

    public function test_admin_can_create_root_category_with_tolerances_and_spec_schema(): void
    {
        $specSchema = [
            ['nama' => 'Kadar Air', 'tipe' => 'number', 'satuan' => '%', 'wajib' => true],
            ['nama' => 'Warna', 'tipe' => 'text', 'satuan' => '', 'wajib' => false],
        ];

        $payload = [
            'nama' => 'Logam Aluminium Scrap',
            'categories_id_categories' => null,
            'toleransi_susut_persen' => 1.50,
            'kadar_air_maks_persen' => 2.00,
            'toleransi_kadar_air_poin' => 0.50,
            'tingkat_risiko' => 'RENDAH',
            'wajib_sertifikat_lab' => 0,
            'is_b3_prohibited' => 0,
            'spec_schema' => $specSchema,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/admin/kategori', $payload);

        $response->assertRedirect('/admin/kategori');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'nama' => 'Logam Aluminium Scrap',
            'toleransi_susut_persen' => 1.50,
            'kadar_air_maks_persen' => 2.00,
            'is_b3_prohibited' => 0,
            'status' => 'AKTIF',
        ]);

        $created = Category::where('nama', 'Logam Aluminium Scrap')->first();
        $this->assertNotNull($created);
        $this->assertEquals($specSchema, $created->spec_schema);
    }

    public function test_admin_can_create_hierarchical_subcategory(): void
    {
        $parent = Category::create([
            'nama' => 'Kertas & Karton',
            'toleransi_susut_persen' => 3.00,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'AKTIF',
        ]);

        $payload = [
            'nama' => 'Karton Box Duplex',
            'categories_id_categories' => $parent->id_categories,
            'toleransi_susut_persen' => 2.00,
            'kadar_air_maks_persen' => 8.00,
            'toleransi_kadar_air_poin' => 1.00,
            'tingkat_risiko' => 'RENDAH',
            'wajib_sertifikat_lab' => 0,
            'is_b3_prohibited' => 0,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/admin/kategori', $payload);

        $response->assertRedirect('/admin/kategori');

        $child = Category::where('nama', 'Karton Box Duplex')->first();
        $this->assertNotNull($child);
        $this->assertEquals($parent->id_categories, $child->categories_id_categories);
        $this->assertEquals($parent->id_categories, $child->parent->id_categories);
    }

    public function test_admin_can_update_category_tolerances_and_b3_flag(): void
    {
        $category = Category::create([
            'nama' => 'Abu Pembakaran Slag',
            'toleransi_susut_persen' => 5.00,
            'tingkat_risiko' => 'RENDAH',
            'is_b3_prohibited' => 0,
            'status' => 'AKTIF',
        ]);

        $payload = [
            'nama' => 'Abu Pembakaran Terindikasi B3',
            'categories_id_categories' => null,
            'toleransi_susut_persen' => 4.00,
            'kadar_air_maks_persen' => 10.00,
            'toleransi_kadar_air_poin' => 2.00,
            'tingkat_risiko' => 'TINGGI',
            'wajib_sertifikat_lab' => 1,
            'is_b3_prohibited' => 1, // Tandai terlarang B3
            'status' => 'AKTIF',
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/admin/kategori/{$category->id_categories}", $payload);

        $response->assertRedirect('/admin/kategori');
        $response->assertSessionHas('success');

        $updated = $category->fresh();
        $this->assertEquals('Abu Pembakaran Terindikasi B3', $updated->nama);
        $this->assertEquals('4.00', $updated->toleransi_susut_persen);
        $this->assertEquals('TINGGI', $updated->tingkat_risiko);
        $this->assertTrue((bool) $updated->is_b3_prohibited);
        $this->assertTrue((bool) $updated->wajib_sertifikat_lab);
    }

    public function test_cannot_archive_category_if_has_active_subcategories(): void
    {
        $parent = Category::create([
            'nama' => 'Induk Plastik',
            'toleransi_susut_persen' => 2.00,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'AKTIF',
        ]);

        Category::create([
            'nama' => 'Sub HDPE',
            'categories_id_categories' => $parent->id_categories,
            'toleransi_susut_persen' => 1.50,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'AKTIF',
        ]);

        $service = app(CategoryService::class);
        $this->assertFalse($service->canArchive($parent));

        $response = $this->actingAs($this->adminUser)
            ->post("/admin/kategori/{$parent->id_categories}/arsip");

        $response->assertSessionHas('error');
        $this->assertEquals('AKTIF', $parent->fresh()->status);
    }

    public function test_cannot_archive_category_if_has_active_listings(): void
    {
        $category = Category::create([
            'nama' => 'Logam Tembaga',
            'toleransi_susut_persen' => 1.00,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'AKTIF',
        ]);

        Listing::create([
            'judul' => 'Sisa Kawat Tembaga Pabrik',
            'volume_kg' => 1000.00,
            'stok_kg' => 1000.00,
            'harga_per_kg' => 90000.00,
            'status' => 'PUBLISHED',
            'companies_id_company' => 1,
            'categories_id_categories' => $category->id_categories,
        ]);

        $service = app(CategoryService::class);
        $this->assertFalse($service->canArchive($category));

        $response = $this->actingAs($this->adminUser)
            ->post("/admin/kategori/{$category->id_categories}/arsip");

        $response->assertSessionHas('error');
        $this->assertEquals('AKTIF', $category->fresh()->status);
    }

    public function test_can_archive_category_when_not_in_use(): void
    {
        $category = Category::create([
            'nama' => 'Tekstil Katun Perca',
            'toleransi_susut_persen' => 2.00,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'AKTIF',
        ]);

        $service = app(CategoryService::class);
        $this->assertTrue($service->canArchive($category));

        $response = $this->actingAs($this->adminUser)
            ->post("/admin/kategori/{$category->id_categories}/arsip");

        $response->assertSessionHas('success');
        $this->assertEquals('ARSIP', $category->fresh()->status);
    }

    public function test_admin_can_unarchive_category(): void
    {
        $category = Category::create([
            'nama' => 'Limbah Kaca Bening',
            'toleransi_susut_persen' => 1.00,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'ARSIP',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post("/admin/kategori/{$category->id_categories}/pulihkan");

        $response->assertSessionHas('success');
        $this->assertEquals('AKTIF', $category->fresh()->status);
    }

    public function test_admin_can_view_create_show_and_edit_pages(): void
    {
        $category = Category::create([
            'nama' => 'Kayu Palet Bekas',
            'toleransi_susut_persen' => 3.00,
            'tingkat_risiko' => 'RENDAH',
            'status' => 'AKTIF',
            'spec_schema' => [
                ['nama' => 'Dimensi', 'tipe' => 'text', 'satuan' => 'cm', 'wajib' => true],
            ],
        ]);

        // Create page
        $this->actingAs($this->adminUser)
            ->get('/admin/kategori/create')
            ->assertStatus(200)
            ->assertSee('Tambah Kategori Material');

        // Show page
        $this->actingAs($this->adminUser)
            ->get("/admin/kategori/{$category->id_categories}")
            ->assertStatus(200)
            ->assertSee('Kayu Palet Bekas')
            ->assertSee('Dimensi');

        // Edit page
        $this->actingAs($this->adminUser)
            ->get("/admin/kategori/{$category->id_categories}/edit")
            ->assertStatus(200)
            ->assertSee('Edit Kategori Material')
            ->assertSee('Kayu Palet Bekas');
    }

    public function test_validation_errors_for_invalid_tolerances_and_fields(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/admin/kategori', [
                'nama' => '', // required
                'toleransi_susut_persen' => 150, // max 100
                'kadar_air_maks_persen' => -5, // min 0
                'tingkat_risiko' => 'INVALID_RISK', // in:RENDAH,TINGGI
            ]);

        $response->assertSessionHasErrors([
            'nama',
            'toleransi_susut_persen',
            'kadar_air_maks_persen',
            'tingkat_risiko',
        ]);
    }
}
