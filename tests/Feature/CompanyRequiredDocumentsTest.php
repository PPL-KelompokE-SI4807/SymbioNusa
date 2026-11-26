<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * PBI-08 (SYM-12): dokumen wajib per tipe mitra, tinjauan ulang otomatis,
 * dan gerbang "hanya perusahaan terverifikasi yang bertransaksi".
 */
#[Group('SYM-12')]
class CompanyRequiredDocumentsTest extends TestCase
{
    use DatabaseTransactions;

    private function addDocument(Company $company, string $jenis, ?string $berlakuSampai = null): CompanyDocument
    {
        return $company->documents()->create([
            'jenis' => $jenis,
            'file_url' => 'company-documents/'.strtolower($jenis).'.pdf',
            'berlaku_sampai' => $berlakuSampai,
            'status' => CompanyDocument::STATUS_PENDING,
        ]);
    }

    public function test_admin_tidak_dapat_menyetujui_seller_tanpa_surat_non_b3(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->seller()->pending()->create();
        $this->addDocument($company, 'NIB');
        $this->addDocument($company, 'IZIN_LINGKUNGAN');

        $this->actingAs($admin)
            ->from(route('admin.companies.show', $company))
            ->post(route('admin.companies.approve', $company))
            ->assertRedirect(route('admin.companies.show', $company))
            ->assertSessionHas('error', fn (string $msg) => str_contains($msg, 'Surat Pernyataan Non-B3'));

        $this->assertSame(Company::STATUS_PENDING, $company->fresh()->status_verifikasi);
    }

    public function test_buyer_wajib_dokumen_pemanfaatan_dan_amdal_diterima_sebagai_dokumen_lingkungan(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->buyer()->pending()->create();
        $this->addDocument($company, 'NIB');
        $this->addDocument($company, 'AMDAL_UKL_UPL');

        $this->actingAs($admin)->post(route('admin.companies.approve', $company));
        $this->assertSame(Company::STATUS_PENDING, $company->fresh()->status_verifikasi);

        $this->addDocument($company, 'DOKUMEN_PEMANFAATAN');

        $this->actingAs($admin)
            ->post(route('admin.companies.approve', $company))
            ->assertRedirect(route('admin.companies.index'));
        $this->assertSame(Company::STATUS_VERIFIED, $company->fresh()->status_verifikasi);
    }

    public function test_dokumen_wajib_kedaluwarsa_tidak_dihitung_saat_persetujuan(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->seller()->pending()->create();
        $this->addDocument($company, 'NIB');
        $this->addDocument($company, 'SURAT_NON_B3');
        $izin = $this->addDocument($company, 'IZIN_LINGKUNGAN', now()->subDay()->toDateString());

        $this->actingAs($admin)->post(route('admin.companies.approve', $company));

        $this->assertSame(Company::STATUS_PENDING, $company->fresh()->status_verifikasi);
        $this->assertSame(CompanyDocument::STATUS_EXPIRED, $izin->fresh()->status);
    }

    public function test_perintah_terjadwal_mengembalikan_perusahaan_ke_tinjauan_ulang(): void
    {
        $expiring = Company::factory()->seller()->create();
        $this->addDocument($expiring, 'NIB');
        $this->addDocument($expiring, 'SURAT_NON_B3');
        $this->addDocument($expiring, 'IZIN_LINGKUNGAN', now()->subDays(2)->toDateString());

        // Dokumen opsional yang kedaluwarsa tidak memicu tinjauan ulang.
        $optional = Company::factory()->seller()->create();
        $this->addDocument($optional, 'NIB');
        $this->addDocument($optional, 'SURAT_NON_B3');
        $this->addDocument($optional, 'IZIN_LINGKUNGAN', now()->addYear()->toDateString());
        $this->addDocument($optional, 'NPWP', now()->subDays(2)->toDateString());

        $this->artisan('perusahaan:tinjau-ulang-kedaluwarsa')->assertSuccessful();

        $this->assertSame(Company::STATUS_PENDING, $expiring->fresh()->status_verifikasi);
        $this->assertSame(Company::STATUS_VERIFIED, $optional->fresh()->status_verifikasi);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('admin.companies.show', $expiring))
            ->assertOk()
            ->assertSee('Tinjauan Ulang');
    }

    public function test_jenis_dokumen_baru_dapat_diunggah(): void
    {
        $user = User::factory()->buyer()->create();

        $this->actingAs($user)
            ->post(route('companies.documents.store'), [
                'jenis' => 'DOKUMEN_PEMANFAATAN',
                'dokumen' => UploadedFile::fake()->create('pemanfaatan.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('company_documents', [
            'id_company' => $user->id_company,
            'jenis' => 'DOKUMEN_PEMANFAATAN',
        ]);
    }

    public function test_perusahaan_belum_terverifikasi_tidak_dapat_membuat_kebutuhan_rfq_atau_po(): void
    {
        $company = Company::factory()->buyer()->pending()->create();
        $buyer = User::factory()->buyer($company)->create();

        foreach (['buyer.demands.create', 'rfq.create', 'po.create'] as $route) {
            $this->actingAs($buyer)
                ->get(route($route))
                ->assertRedirect(route('companies.edit'));
        }

        $this->actingAs($buyer)
            ->post(route('buyer.demands.store'), [])
            ->assertRedirect(route('companies.edit'));
    }

    public function test_perusahaan_terverifikasi_tetap_dapat_membuka_form_kebutuhan(): void
    {
        $buyer = User::factory()->buyer()->create();

        $this->actingAs($buyer)
            ->get(route('buyer.demands.create'))
            ->assertOk();
    }
}
