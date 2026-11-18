<?php

namespace Tests\Feature;

use App\Enums\InspectorStatus;
use App\Models\Category;
use App\Models\InspectorProfile;
use App\Models\User;
use App\Services\Inspector\InspectorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * PBI-01 (SYM-5): Registri Inspector & Sertifikasi Kompetensi.
 */
#[Group('SYM-5')]
class InspectorRegistryTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected Category $plastik;

    protected Category $tekstil;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(InspectorService::DISK);

        $this->admin = User::factory()->admin()->create();
        $this->plastik = Category::factory()->create(['nama' => 'Plastik Uji SYM5', 'status' => 'AKTIF', 'is_b3_prohibited' => 0]);
        $this->tekstil = Category::factory()->create(['nama' => 'Tekstil Uji SYM5', 'status' => 'AKTIF', 'is_b3_prohibited' => 0]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(User $inspectorUser, array $overrides = []): array
    {
        return array_merge([
            'id_user' => $inspectorUser->id_user,
            'bidang_keahlian' => [$this->plastik->id_categories, $this->tekstil->id_categories],
            'wilayah_tugas' => 'Jawa Barat',
            'no_sertifikat' => 'INS-UJI-2026-001',
            'berlaku_sampai' => InspectorProfile::today()->addYear()->toDateString(),
            'sertifikat' => UploadedFile::fake()->create('sertifikat.pdf', 200, 'application/pdf'),
        ], $overrides);
    }

    // ---- Akses ------------------------------------------------------------

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('admin.inspectors.index'))->assertRedirect(route('login'));
    }

    public function test_hanya_admin_yang_bisa_membuka_dan_mengelola_registri(): void
    {
        $seller = User::factory()->seller()->create();
        $inspector = User::factory()->inspector()->create();
        $profile = InspectorProfile::factory()->create();

        foreach ([$seller, $inspector] as $user) {
            $this->actingAs($user)->get(route('admin.inspectors.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.inspectors.create'))->assertForbidden();
            $this->actingAs($user)->post(route('admin.inspectors.store'), $this->validPayload($inspector))->assertForbidden();
            $this->actingAs($user)->post(route('admin.inspectors.deactivate', $profile), ['alasan' => 'Alasan yang cukup panjang'])->assertForbidden();
            $this->actingAs($user)->post(route('admin.inspectors.renew', $profile), [
                'no_sertifikat' => 'X', 'berlaku_sampai' => InspectorProfile::today()->addYear()->toDateString(),
            ])->assertForbidden();
        }

        $this->assertTrue($profile->fresh()->status_aktif);
    }

    // ---- Daftar & filter -------------------------------------------------

    public function test_admin_melihat_daftar_dengan_status_turunan_dan_kpi(): void
    {
        InspectorProfile::factory()->withExpertise([$this->plastik->id_categories])->create(['wilayah_tugas' => 'Banten']);
        InspectorProfile::factory()->expiringSoon(5)->create();
        InspectorProfile::factory()->expired()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.inspectors.index'));

        $response->assertOk()
            ->assertSee('Registri Inspector')
            ->assertSee('Daftarkan Inspector')
            ->assertSee('Segera Berakhir')
            ->assertSee('Nonaktif')
            ->assertSee('Plastik Uji SYM5');
    }

    public function test_filter_status_keahlian_dan_wilayah(): void
    {
        $plastikBanten = InspectorProfile::factory()->withExpertise([$this->plastik->id_categories])->create(['wilayah_tugas' => 'Banten']);
        $tekstilBali = InspectorProfile::factory()->withExpertise([$this->tekstil->id_categories])->create(['wilayah_tugas' => 'Bali']);
        $expired = InspectorProfile::factory()->expired()->withExpertise([$this->plastik->id_categories])->create(['wilayah_tugas' => 'Banten']);

        $ids = fn ($response) => collect($response->viewData('inspectors')->items())->pluck('id_inspector')->all();

        $byExpertise = $this->actingAs($this->admin)->get(route('admin.inspectors.index', ['keahlian' => $this->plastik->id_categories]));
        $this->assertEqualsCanonicalizing([$plastikBanten->id_inspector, $expired->id_inspector], $ids($byExpertise));

        $byRegion = $this->actingAs($this->admin)->get(route('admin.inspectors.index', ['wilayah' => 'Bali']));
        $this->assertSame([$tekstilBali->id_inspector], $ids($byRegion));

        $byStatus = $this->actingAs($this->admin)->get(route('admin.inspectors.index', ['status' => 'NONAKTIF', 'wilayah' => 'Banten']));
        $this->assertSame([$expired->id_inspector], $ids($byStatus));

        $bySearch = $this->actingAs($this->admin)->get(route('admin.inspectors.index', ['q' => $tekstilBali->no_sertifikat]));
        $this->assertSame([$tekstilBali->id_inspector], $ids($bySearch));
    }

    // ---- Daftarkan -------------------------------------------------------

    public function test_admin_mendaftarkan_inspector_dengan_berkas_sertifikat(): void
    {
        $inspectorUser = User::factory()->inspector()->create(['nama' => 'Dr. Andi Uji']);

        $response = $this->actingAs($this->admin)->post(route('admin.inspectors.store'), $this->validPayload($inspectorUser));

        $profile = InspectorProfile::where('id_user', $inspectorUser->id_user)->firstOrFail();
        $response->assertRedirect(route('admin.inspectors.show', $profile))->assertSessionHas('success');

        $this->assertTrue($profile->status_aktif);
        $this->assertSame('Jawa Barat', $profile->wilayah_tugas);
        $this->assertEqualsCanonicalizing([$this->plastik->id_categories, $this->tekstil->id_categories], $profile->expertiseIds());
        $this->assertSame(InspectorStatus::AKTIF, $profile->status());
        Storage::disk(InspectorService::DISK)->assertExists($profile->sertifikat_url);

        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'INSPECTOR_DIDAFTARKAN',
            'auditable_type' => 'inspector_profiles',
            'auditable_id' => $profile->id_inspector,
            'users_id_user' => $this->admin->id_user,
        ]);
    }

    public function test_validasi_daftarkan_menolak_isian_tidak_sah(): void
    {
        $inspectorUser = User::factory()->inspector()->create();
        $buyer = User::factory()->buyer()->create();
        $b3 = Category::factory()->create(['status' => 'AKTIF', 'is_b3_prohibited' => 1]);
        $arsip = Category::factory()->create(['status' => 'ARSIP', 'is_b3_prohibited' => 0]);

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.store'), [])
            ->assertSessionHasErrors(['id_user', 'bidang_keahlian', 'wilayah_tugas', 'no_sertifikat', 'berlaku_sampai', 'sertifikat']);

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.store'), $this->validPayload($buyer))
            ->assertSessionHasErrors('id_user');

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.store'), $this->validPayload($inspectorUser, ['bidang_keahlian' => [$b3->id_categories, $arsip->id_categories]]))
            ->assertSessionHasErrors(['bidang_keahlian.0', 'bidang_keahlian.1']);

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.store'), $this->validPayload($inspectorUser, [
                'berlaku_sampai' => InspectorProfile::today()->toDateString(),
                'wilayah_tugas' => 'Atlantis',
                'sertifikat' => UploadedFile::fake()->create('sertifikat.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ]))
            ->assertSessionHasErrors(['berlaku_sampai', 'wilayah_tugas', 'sertifikat']);

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.store'), $this->validPayload($inspectorUser, [
                'sertifikat' => UploadedFile::fake()->create('besar.pdf', 6 * 1024, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('sertifikat');

        $this->assertDatabaseMissing('inspector_profiles', ['id_user' => $inspectorUser->id_user]);
    }

    public function test_akun_yang_sudah_terdaftar_dan_nomor_sertifikat_ganda_ditolak(): void
    {
        $existing = InspectorProfile::factory()->create(['no_sertifikat' => 'INS-GANDA-01']);
        $newUser = User::factory()->inspector()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.store'), $this->validPayload($existing->user))
            ->assertSessionHasErrors('id_user');

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.store'), $this->validPayload($newUser, ['no_sertifikat' => 'INS-GANDA-01']))
            ->assertSessionHasErrors('no_sertifikat');
    }

    // ---- Ubah, perpanjang, keluarkan -------------------------------------

    public function test_admin_mengubah_keahlian_dan_wilayah(): void
    {
        $profile = InspectorProfile::factory()->withExpertise([$this->plastik->id_categories])->create(['wilayah_tugas' => 'Banten']);
        $berlaku = $profile->berlaku_sampai->toDateString();

        $this->actingAs($this->admin)
            ->put(route('admin.inspectors.update', $profile), [
                'bidang_keahlian' => [$this->tekstil->id_categories],
                'wilayah_tugas' => 'DI Yogyakarta',
                'no_sertifikat' => 'INS-UBAH-01',
                // Masa berlaku tidak boleh berubah lewat Ubah.
                'berlaku_sampai' => InspectorProfile::today()->addYears(5)->toDateString(),
            ])
            ->assertRedirect(route('admin.inspectors.show', $profile));

        $profile->refresh();
        $this->assertSame([$this->tekstil->id_categories], $profile->expertiseIds());
        $this->assertSame('DI Yogyakarta', $profile->wilayah_tugas);
        $this->assertSame('INS-UBAH-01', $profile->no_sertifikat);
        $this->assertSame($berlaku, $profile->berlaku_sampai->toDateString());
    }

    public function test_perpanjang_mengaktifkan_kembali_inspector_kedaluwarsa(): void
    {
        $profile = InspectorProfile::factory()->expired()->deactivated()->create();
        $baru = InspectorProfile::today()->addYears(2)->toDateString();

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.renew', $profile), [
                'no_sertifikat' => 'INS-BARU-2028',
                'berlaku_sampai' => $baru,
                'sertifikat' => UploadedFile::fake()->create('baru.png', 300, 'image/png'),
            ])
            ->assertRedirect(route('admin.inspectors.show', $profile));

        $profile->refresh();
        $this->assertTrue($profile->status_aktif);
        $this->assertSame($baru, $profile->berlaku_sampai->toDateString());
        $this->assertSame(InspectorStatus::AKTIF, $profile->status());
        Storage::disk(InspectorService::DISK)->assertExists($profile->sertifikat_url);
        $this->assertDatabaseHas('audit_logs', ['aksi' => 'INSPECTOR_DIPERPANJANG', 'auditable_id' => $profile->id_inspector]);
    }

    public function test_perpanjang_menolak_tanggal_yang_sudah_lewat(): void
    {
        $profile = InspectorProfile::factory()->expiringSoon()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.renew', $profile), [
                'no_sertifikat' => $profile->no_sertifikat,
                'berlaku_sampai' => InspectorProfile::today()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors('berlaku_sampai');
    }

    public function test_keluarkan_menonaktifkan_tanpa_menghapus_dan_wajib_alasan(): void
    {
        $profile = InspectorProfile::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.deactivate', $profile), ['alasan' => ''])
            ->assertSessionHasErrors('alasan');
        $this->assertTrue($profile->fresh()->status_aktif);

        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.deactivate', $profile), ['alasan' => 'Kontrak kerja sama berakhir.'])
            ->assertRedirect(route('admin.inspectors.show', $profile));

        $profile->refresh();
        $this->assertFalse($profile->status_aktif);
        $this->assertSame(InspectorStatus::NONAKTIF, $profile->status());
        $this->assertDatabaseHas('inspector_profiles', ['id_inspector' => $profile->id_inspector]);
        $this->assertFalse(InspectorProfile::assignable()->whereKey($profile->id_inspector)->exists());

        $log = DB::table('audit_logs')->where('aksi', 'INSPECTOR_DIKELUARKAN')->where('auditable_id', $profile->id_inspector)->first();
        $this->assertNotNull($log);
        $this->assertSame('Kontrak kerja sama berakhir.', json_decode($log->nilai_baru, true)['alasan']);

        // Sudah nonaktif: tidak bisa dikeluarkan dua kali.
        $this->actingAs($this->admin)
            ->post(route('admin.inspectors.deactivate', $profile), ['alasan' => 'Alasan kedua yang cukup panjang'])
            ->assertForbidden();
    }

    // ---- Otomatis nonaktif & aturan status -------------------------------

    public function test_perintah_terjadwal_menonaktifkan_sertifikat_kedaluwarsa(): void
    {
        $expired = InspectorProfile::factory()->expired()->create();
        $endsToday = InspectorProfile::factory()->create(['berlaku_sampai' => InspectorProfile::today()->toDateString()]);
        $valid = InspectorProfile::factory()->create();

        $this->artisan('inspector:nonaktifkan-kedaluwarsa')->assertSuccessful();

        $this->assertFalse($expired->fresh()->status_aktif);
        $this->assertTrue($endsToday->fresh()->status_aktif, 'Sertifikat masih berlaku sampai akhir hari ini.');
        $this->assertTrue($valid->fresh()->status_aktif);
        $this->assertDatabaseHas('audit_logs', ['aksi' => 'INSPECTOR_KEDALUWARSA', 'auditable_id' => $expired->id_inspector, 'users_id_user' => null]);
    }

    public function test_status_turunan_dan_scope_penugasan(): void
    {
        $today = Carbon::parse('2026-11-19');

        $this->assertSame(InspectorStatus::AKTIF, InspectorStatus::resolve(true, $today->copy()->addDays(31), $today));
        $this->assertSame(InspectorStatus::SEGERA_BERAKHIR, InspectorStatus::resolve(true, $today->copy()->addDays(30), $today));
        $this->assertSame(InspectorStatus::SEGERA_BERAKHIR, InspectorStatus::resolve(true, $today->copy(), $today));
        $this->assertSame(InspectorStatus::NONAKTIF, InspectorStatus::resolve(true, $today->copy()->subDay(), $today));
        $this->assertSame(InspectorStatus::NONAKTIF, InspectorStatus::resolve(false, $today->copy()->addYear(), $today));
        $this->assertSame(InspectorStatus::NONAKTIF, InspectorStatus::resolve(true, null, $today));

        $plastikAktif = InspectorProfile::factory()->withExpertise([$this->plastik->id_categories])->create();
        $plastikExpired = InspectorProfile::factory()->expired()->withExpertise([$this->plastik->id_categories])->create();
        InspectorProfile::factory()->withExpertise([$this->tekstil->id_categories])->create();

        $assignable = InspectorProfile::assignable()->withExpertise($this->plastik->id_categories)->pluck('id_inspector')->all();
        $this->assertSame([$plastikAktif->id_inspector], $assignable);
        $this->assertNotContains($plastikExpired->id_inspector, $assignable);
    }

    // ---- Berkas & Profil Kompetensi Inspector ----------------------------

    public function test_berkas_sertifikat_hanya_untuk_admin_dan_pemilik(): void
    {
        Storage::disk(InspectorService::DISK)->put('inspector-certificates/uji.pdf', '%PDF-1.4 uji');
        $profile = InspectorProfile::factory()->create(['sertifikat_url' => 'inspector-certificates/uji.pdf']);
        $other = User::factory()->inspector()->create();

        $this->actingAs($this->admin)->get(route('inspectors.certificate', $profile))->assertOk()->assertDownload();
        $this->actingAs($profile->user)->get(route('inspectors.certificate', $profile))->assertOk();
        $this->actingAs($other)->get(route('inspectors.certificate', $profile))->assertForbidden();
        $this->actingAs(User::factory()->buyer()->create())->get(route('inspectors.certificate', $profile))->assertForbidden();
    }

    public function test_inspector_melihat_profil_sendiri_dan_hanya_bisa_mengunggah_berkas(): void
    {
        $profile = InspectorProfile::factory()->expiringSoon(7)->withExpertise([$this->plastik->id_categories])->create();
        $berlaku = $profile->berlaku_sampai->toDateString();

        $this->actingAs($profile->user)->get(route('inspector.profile'))
            ->assertOk()
            ->assertSee('Profil Kompetensi')
            ->assertSee('Segera Berakhir')
            ->assertSee('Plastik Uji SYM5');

        $this->actingAs($profile->user)
            ->post(route('inspector.profile.certificate'), [
                'sertifikat' => UploadedFile::fake()->create('perpanjangan.pdf', 150, 'application/pdf'),
                'berlaku_sampai' => InspectorProfile::today()->addYears(3)->toDateString(),
                'status_aktif' => 1,
            ])
            ->assertRedirect(route('inspector.profile'));

        $profile->refresh();
        $this->assertSame($berlaku, $profile->berlaku_sampai->toDateString());
        Storage::disk(InspectorService::DISK)->assertExists($profile->sertifikat_url);
        $this->assertDatabaseHas('audit_logs', ['aksi' => 'INSPECTOR_UNGGAH_SERTIFIKAT', 'users_id_user' => $profile->id_user]);

        $this->actingAs(User::factory()->seller()->create())->get(route('inspector.profile'))->assertForbidden();
    }

    public function test_inspector_tanpa_profil_melihat_keadaan_kosong(): void
    {
        $user = User::factory()->inspector()->create();

        $this->actingAs($user)->get(route('inspector.profile'))
            ->assertOk()
            ->assertSee('belum terdaftar di registri Inspector');

        $this->actingAs($user)
            ->post(route('inspector.profile.certificate'), ['sertifikat' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertForbidden();
    }
}
