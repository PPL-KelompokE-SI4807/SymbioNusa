<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyVerificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_perusahaan_dapat_memperbarui_profil_dan_menyetujui_syarat_ketentuan(): void
    {
        $company = Company::create([
            'nama' => 'PT Awal Industri',
            'tipe' => 'SELLER',
            'status_verifikasi' => 'PENDING',
        ]);

        $user = User::where('email', 'seller@symbionusa.test')->firstOrFail();
        $user->forceFill(['id_company' => $company->id_company])->save();

        $nibBaru = '912030' . random_int(1000000, 9999999);

        $response = $this->actingAs($user)->put(route('companies.update'), [
            'nama' => 'PT Plastik Makmur Baru',
            'tipe' => 'KEDUANYA',
            'nib' => $nibBaru,
            'alamat' => 'Jl. Industri Raya No. 10, Bandung',
            'kontak' => '081234567890',
            'lat' => -6.917464,
            'lng' => 107.619123,
            'tos_accepted' => '1',
        ]);

        $response->assertRedirect(route('companies.edit'));
        $this->assertDatabaseHas('companies', [
            'id_company' => $company->id_company,
            'nama' => 'PT Plastik Makmur Baru',
            'tipe' => 'KEDUANYA',
            'nib' => $nibBaru,
            'tos_versi' => 'v1.0-2026',
        ]);
    }

    public function test_perusahaan_dapat_mengunggah_dokumen_legalitas_valid_dan_menolak_berkas_tidak_sesuai(): void
    {
        Storage::fake('public');

        $company = Company::create([
            'nama' => 'CV Sumber Kertas',
            'tipe' => 'SELLER',
            'status_verifikasi' => 'PENDING',
        ]);

        $user = User::where('email', 'seller@symbionusa.test')->firstOrFail();
        $user->forceFill(['id_company' => $company->id_company])->save();

        $validPdf = UploadedFile::fake()->create('nib-resmi.pdf', 1024, 'application/pdf');

        $this->actingAs($user)
            ->post(route('companies.documents.store'), [
                'jenis' => 'NIB',
                'dokumen' => $validPdf,
            ])
            ->assertRedirect(route('companies.edit'));

        $this->assertDatabaseHas('company_documents', [
            'id_company' => $company->id_company,
            'jenis' => 'NIB',
            'status' => 'PENDING',
        ]);

        $invalidExe = UploadedFile::fake()->create('malware.exe', 500);

        $this->actingAs($user)
            ->post(route('companies.documents.store'), [
                'jenis' => 'NIB',
                'dokumen' => $invalidExe,
            ])
            ->assertSessionHasErrors('dokumen');
    }

    public function test_perusahaan_bertipe_keduanya_dapat_beralih_peran_aktif_antara_seller_dan_buyer(): void
    {
        $company = Company::create([
            'nama' => 'PT Sirkular Nusantara',
            'tipe' => 'KEDUANYA',
            'status_verifikasi' => 'TERVERIFIKASI',
        ]);

        $user = User::where('email', 'seller@symbionusa.test')->firstOrFail();
        $user->forceFill(['id_company' => $company->id_company])->save();

        $this->actingAs($user)
            ->post(route('companies.switch-role'), ['role' => 'BUYER'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame('BUYER', $user->fresh()->activeRole());
    }

    public function test_admin_dapat_menyetujui_dan_menolak_verifikasi_perusahaan(): void
    {
        $admin = User::where('email', 'admin@symbionusa.test')->firstOrFail();

        $company = Company::create([
            'nama' => 'CV Kertas Lestari',
            'tipe' => 'SELLER',
            'status_verifikasi' => 'PENDING',
        ]);

        $document = $company->documents()->create([
            'jenis' => 'NIB',
            'file_url' => 'company-documents/sample.pdf',
            'status' => 'PENDING',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.companies.approve', $company))
            ->assertRedirect(route('admin.companies.index'));

        $this->assertSame('TERVERIFIKASI', $company->fresh()->status_verifikasi);
        $this->assertSame('VALID', $document->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.companies.reject', $company), [
                'alasan_penolakan' => 'Dokumen NIB buram, harap unggah ulang.',
            ])
            ->assertRedirect(route('admin.companies.index'));

        $this->assertSame('DITOLAK', $company->fresh()->status_verifikasi);
    }
}