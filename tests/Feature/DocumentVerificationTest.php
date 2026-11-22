<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\DocumentVerification;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('SYM-21')]
class DocumentVerificationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $inspector;

    protected User $otherInspector;

    protected User $seller;

    protected User $buyer;

    protected Company $sellerCompany;

    protected Company $buyerCompany;

    protected PurchaseOrder $purchaseOrder;

    protected DocumentVerification $verification;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerCompany = Company::factory()->seller()->create();
        $this->buyerCompany = Company::factory()->buyer()->create();

        $this->seller = User::factory()->seller($this->sellerCompany)->create();
        $this->buyer = User::factory()->buyer($this->buyerCompany)->create();

        $this->inspector = User::factory()->inspector()->create();
        $this->otherInspector = User::factory()->inspector()->create();

        $contract = RfqContract::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        $this->purchaseOrder = PurchaseOrder::factory()->create([
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
            'rfq_contracts_id_rfq_contract' => $contract->id_rfq_contract,
            'status' => 'INSPEKSI',
        ]);

        $this->verification = DocumentVerification::factory()->create([
            'purchase_orders_id_purchase_order' => $this->purchaseOrder->id_purchase_order,
            'users_id_user' => $this->inspector->id_user,
            'status' => DocumentVerification::STATUS_MENUNGGU,
        ]);
    }

    public function test_inspector_dapat_melihat_daftar_tugas_verifikasi_dokumen(): void
    {
        $response = $this->actingAs($this->inspector)
            ->get(route('inspector.verifications.index'));

        $response->assertOk();
        $response->assertSee('Verifikasi Dokumen');
        $response->assertSee('PO #'.$this->purchaseOrder->id_purchase_order);
        $response->assertSee($this->sellerCompany->nama);
    }

    public function test_inspector_dapat_menyaring_daftar_verifikasi_berdasarkan_status(): void
    {
        // Buat verifikasi berstatus SETUJU untuk inspector yang sama
        $verifiedPo = PurchaseOrder::factory()->create([
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
        ]);
        DocumentVerification::factory()->setuju()->create([
            'purchase_orders_id_purchase_order' => $verifiedPo->id_purchase_order,
            'users_id_user' => $this->inspector->id_user,
        ]);

        // Filter MENUNGGU
        $responseMenunggu = $this->actingAs($this->inspector)
            ->get(route('inspector.verifications.index', ['status' => 'MENUNGGU']));
        $responseMenunggu->assertOk();
        $responseMenunggu->assertSee('PO #'.$this->purchaseOrder->id_purchase_order);
        $responseMenunggu->assertDontSee('PO #'.$verifiedPo->id_purchase_order);

        // Filter SETUJU
        $responseSetuju = $this->actingAs($this->inspector)
            ->get(route('inspector.verifications.index', ['status' => 'SETUJU']));
        $responseSetuju->assertOk();
        $responseSetuju->assertSee('PO #'.$verifiedPo->id_purchase_order);
        $responseSetuju->assertDontSee('PO #'.$this->purchaseOrder->id_purchase_order);
    }

    public function test_inspector_ditugaskan_dapat_melihat_halaman_detail_verifikasi(): void
    {
        // Tambahkan dokumen legalitas dummy
        CompanyDocument::create([
            'id_company' => $this->sellerCompany->id_company,
            'jenis' => 'NIB',
            'file_url' => 'company-documents/nib.pdf',
            'status' => CompanyDocument::STATUS_VALID,
            'berlaku_sampai' => now()->addYear()->toDateString(),
        ]);

        $response = $this->actingAs($this->inspector)
            ->get(route('inspector.verifications.show', $this->verification));

        $response->assertOk();
        $response->assertSee('Dokumen Legalitas');
        $response->assertSee('Checklist Verifikasi');
        $response->assertSee('Nama perusahaan sesuai dokumen');
        $response->assertSee('Nomor dokumen valid');
        $response->assertSee('Masa berlaku belum habis');
        $response->assertSee('Bukti listing sesuai produk');
        $response->assertSee('Dokumen terbaca jelas');
        $response->assertSee($this->sellerCompany->nama);
    }

    public function test_surat_pernyataan_non_b3_tampil_di_dokumen_legalitas_dengan_tautan_storage(): void
    {
        CompanyDocument::create([
            'id_company' => $this->sellerCompany->id_company,
            'jenis' => 'SURAT_NON_B3',
            'file_url' => 'company-documents/surat-non-b3.pdf',
            'status' => CompanyDocument::STATUS_VALID,
            'berlaku_sampai' => now()->addYear()->toDateString(),
        ]);

        $response = $this->actingAs($this->inspector)
            ->get(route('inspector.verifications.show', $this->verification));

        $response->assertOk();
        $response->assertSee('Dokumen Legalitas (1)');
        $response->assertSee('SURAT_NON_B3');
        $response->assertSee(asset('storage/company-documents/surat-non-b3.pdf'), false);
    }

    public function test_inspector_ditugaskan_dapat_menyetujui_verifikasi_dokumen_dengan_checklist(): void
    {
        $payload = [
            'status' => DocumentVerification::STATUS_SETUJU,
            'checklist' => [
                'nama_perusahaan_sesuai' => '1',
                'nomor_dokumen_valid' => '1',
                'masa_berlaku_valid' => '1',
                'bukti_listing_sesuai' => '1',
                'dokumen_terbaca_jelas' => '1',
            ],
            'alasan' => 'Dokumen lengkap dan valid.',
        ];

        $response = $this->actingAs($this->inspector)
            ->post(route('inspector.verifications.verify', $this->verification), $payload);

        $response->assertRedirect(route('inspector.verifications.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('document_verifications', [
            'id_document_verification' => $this->verification->id_document_verification,
            'status' => DocumentVerification::STATUS_SETUJU,
            'alasan' => null, // saat disetujui alasan dikosongkan/null sesuai aturan
        ]);

        $this->verification->refresh();
        $this->assertEquals(DocumentVerification::STATUS_SETUJU, $this->verification->status);
        $this->assertIsArray($this->verification->checklist);
        $this->assertEquals('1', $this->verification->checklist['nama_perusahaan_sesuai']);
    }

    public function test_inspector_ditugaskan_dapat_menolak_verifikasi_dokumen_dengan_catatan(): void
    {
        $catatanPenolakan = 'Surat izin lingkungan kadaluwarsa per tanggal 1 Oktober 2026.';
        $payload = [
            'status' => DocumentVerification::STATUS_DITOLAK,
            'checklist' => [
                'nama_perusahaan_sesuai' => '1',
                'nomor_dokumen_valid' => '1',
                'masa_berlaku_valid' => '0',
                'bukti_listing_sesuai' => '1',
                'dokumen_terbaca_jelas' => '1',
            ],
            'alasan' => $catatanPenolakan,
        ];

        $response = $this->actingAs($this->inspector)
            ->post(route('inspector.verifications.verify', $this->verification), $payload);

        $response->assertRedirect(route('inspector.verifications.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('document_verifications', [
            'id_document_verification' => $this->verification->id_document_verification,
            'status' => DocumentVerification::STATUS_DITOLAK,
            'alasan' => $catatanPenolakan,
        ]);
    }

    public function test_penolakan_verifikasi_gagal_bila_alasan_kosong(): void
    {
        $payload = [
            'status' => DocumentVerification::STATUS_DITOLAK,
            'alasan' => '',
        ];

        $response = $this->actingAs($this->inspector)
            ->from(route('inspector.verifications.show', $this->verification))
            ->post(route('inspector.verifications.verify', $this->verification), $payload);

        $response->assertRedirect(route('inspector.verifications.show', $this->verification));
        $response->assertSessionHasErrors(['alasan']);

        $this->assertDatabaseHas('document_verifications', [
            'id_document_verification' => $this->verification->id_document_verification,
            'status' => DocumentVerification::STATUS_MENUNGGU,
        ]);
    }

    public function test_pengguna_bukan_inspector_ditolak_mengakses_verifikasi_dokumen(): void
    {
        // Buyer mencoba melihat daftar
        $this->actingAs($this->buyer)
            ->get(route('inspector.verifications.index'))
            ->assertForbidden();

        // Seller mencoba melihat detail
        $this->actingAs($this->seller)
            ->get(route('inspector.verifications.show', $this->verification))
            ->assertForbidden();

        // Buyer mencoba memverifikasi
        $this->actingAs($this->buyer)
            ->post(route('inspector.verifications.verify', $this->verification), [
                'status' => DocumentVerification::STATUS_SETUJU,
            ])
            ->assertForbidden();
    }

    public function test_inspector_lain_tidak_boleh_memutuskan_verifikasi_milik_inspector_lain(): void
    {
        // otherInspector melihat verifikasi yang bukan miliknya
        $this->actingAs($this->otherInspector)
            ->get(route('inspector.verifications.show', $this->verification))
            ->assertForbidden();

        // otherInspector mencoba menyetujui
        $this->actingAs($this->otherInspector)
            ->post(route('inspector.verifications.verify', $this->verification), [
                'status' => DocumentVerification::STATUS_SETUJU,
            ])
            ->assertForbidden();
    }

    public function test_verifikasi_yang_sudah_diputuskan_tidak_dapat_diverifikasi_ulang(): void
    {
        // Ubah status verification menjadi SETUJU
        $this->verification->update(['status' => DocumentVerification::STATUS_SETUJU]);

        $this->actingAs($this->inspector)
            ->post(route('inspector.verifications.verify', $this->verification), [
                'status' => DocumentVerification::STATUS_DITOLAK,
                'alasan' => 'Mencoba menolak verifikasi yang sudah disetujui sebelumnya.',
            ])
            ->assertForbidden();
    }
}
