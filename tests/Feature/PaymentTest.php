<?php

namespace Tests\Feature;

use App\Enums\PaymentVerificationStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('SYM-11')]
class PaymentTest extends TestCase
{
    use DatabaseTransactions;

    protected User $buyer;

    protected User $seller;

    protected Company $buyerCompany;

    protected Company $sellerCompany;

    protected PurchaseOrder $purchaseOrder;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->buyerCompany = Company::factory()->buyer()->create();
        $this->sellerCompany = Company::factory()->seller()->create();

        $this->buyer = User::factory()->buyer($this->buyerCompany)->create();
        $this->seller = User::factory()->seller($this->sellerCompany)->create();

        $contract = RfqContract::factory()->create([
            'companies_id_company' => $this->buyerCompany->id_company,
        ]);

        $this->purchaseOrder = PurchaseOrder::factory()->create([
            'companies_id_buyer' => $this->buyerCompany->id_company,
            'companies_id_seller' => $this->sellerCompany->id_company,
            'rfq_contracts_id_rfq_contract' => $contract->id_rfq_contract,
            'status' => 'INVOICED',
        ]);

        $this->invoice = Invoice::factory()->create([
            'purchase_orders_id_purchase_order' => $this->purchaseOrder->id_purchase_order,
            'total' => 15000000.00,
            'status' => 'TERBIT',
        ]);
    }

    /**
     * AC 1: Buyer dapat mengakses formulir unggah bukti pembayaran.
     */
    public function test_buyer_dapat_melihat_halaman_unggah_bukti_pembayaran(): void
    {
        $response = $this->actingAs($this->buyer)
            ->get(route('buyer.invoices.pay', $this->invoice));

        $response->assertOk()
            ->assertSee('Unggah Bukti Pembayaran')
            ->assertSee($this->invoice->nomor);
    }

    /**
     * AC 1 & AC 2: Buyer dapat mengunggah bukti transfer yang valid dengan nominal sesuai.
     */
    public function test_buyer_dapat_mengunggah_bukti_transfer_dengan_data_valid(): void
    {
        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 200, 'image/jpeg');

        $payload = [
            'tgl_transfer' => now()->toDateString(),
            'nominal' => 15000000.00,
            'bank_asal' => 'BCA',
            'nomor_rekening_asal' => '1234567890',
            'nama_rekening_asal' => 'PT Pembeli Sukses',
            'bukti' => $file,
        ];

        $response = $this->actingAs($this->buyer)
            ->post(route('buyer.invoices.store', $this->invoice), $payload);

        $response->assertRedirect(route('buyer.invoices.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'invoices_id_invoice' => $this->invoice->id_invoice,
            'nominal' => 15000000.00,
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU->value,
        ]);

        $payment = Payment::where('invoices_id_invoice', $this->invoice->id_invoice)->first();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->bukti_url);
        Storage::disk('local')->assertExists($payment->bukti_url);

        // Notifikasi ke Seller tercatat
        $this->assertDatabaseHas('notifications', [
            'users_id_user' => $this->seller->id_user,
            'jenis' => 'pembayaran.diunggah',
        ]);
    }

    /**
     * AC 2: Nominal transfer wajib sama persis dengan total tagihan invoice.
     */
    public function test_unggah_bukti_ditolak_jika_nominal_tidak_sama_dengan_invoice(): void
    {
        $file = UploadedFile::fake()->create('bukti_transfer.png', 150, 'image/png');

        $payload = [
            'tgl_transfer' => now()->toDateString(),
            'nominal' => 10000000.00, // Berbeda dari total invoice (15.000.000)
            'bank_asal' => 'Mandiri',
            'nomor_rekening_asal' => '9876543210',
            'nama_rekening_asal' => 'PT Pembeli Sukses',
            'bukti' => $file,
        ];

        $response = $this->actingAs($this->buyer)
            ->post(route('buyer.invoices.store', $this->invoice), $payload);

        $response->assertSessionHasErrors('nominal');
        $this->assertDatabaseMissing('payments', [
            'invoices_id_invoice' => $this->invoice->id_invoice,
        ]);
    }

    /**
     * AC 2: Bukti transfer harus berupa format PDF/JPG/PNG.
     */
    public function test_unggah_bukti_ditolak_jika_format_file_bukan_gambar_atau_pdf(): void
    {
        $file = UploadedFile::fake()->create('dokumen.txt', 50, 'text/plain');

        $payload = [
            'tgl_transfer' => now()->toDateString(),
            'nominal' => 15000000.00,
            'bank_asal' => 'BNI',
            'nomor_rekening_asal' => '5555666677',
            'nama_rekening_asal' => 'PT Pembeli Sukses',
            'bukti' => $file,
        ];

        $response = $this->actingAs($this->buyer)
            ->post(route('buyer.invoices.store', $this->invoice), $payload);

        $response->assertSessionHasErrors('bukti');
    }

    /**
     * AC 3: Buyer dapat mengganti bukti transfer yang masih berstatus MENUNGGU atau DITOLAK.
     */
    public function test_buyer_dapat_mengganti_bukti_sebelum_diverifikasi_valid(): void
    {
        $payment = Payment::factory()->create([
            'invoices_id_invoice' => $this->invoice->id_invoice,
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
        ]);

        $this->assertTrue($this->buyer->can('update', $payment));

        // Tetapi jika sudah VALID, tidak dapat diubah
        $payment->status_verifikasi = PaymentVerificationStatus::VALID;
        $payment->save();

        $this->assertFalse($this->buyer->can('update', $payment));
        $this->assertFalse($this->buyer->can('create', [Payment::class, $this->invoice]));
    }

    /**
     * AC 4: Seller dapat melihat antrean dan detail verifikasi pembayaran.
     */
    public function test_seller_dapat_melihat_antrean_dan_halaman_verifikasi_pembayaran(): void
    {
        $payment = Payment::factory()->create([
            'invoices_id_invoice' => $this->invoice->id_invoice,
            'nominal' => 15000000.00,
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
        ]);

        $response = $this->actingAs($this->seller)
            ->get(route('seller.payments.verify', $payment));

        $response->assertOk()
            ->assertSee('Verifikasi Bukti Pembayaran')
            ->assertSee($this->invoice->nomor)
            ->assertSee('Komparasi Nominal');
    }

    /**
     * AC 5: Seller memverifikasi VALID, status invoice dan purchase order menjadi DIBAYAR.
     */
    public function test_seller_dapat_memverifikasi_valid_dan_status_order_berubah_menjadi_dibayar(): void
    {
        $payment = Payment::factory()->create([
            'invoices_id_invoice' => $this->invoice->id_invoice,
            'nominal' => 15000000.00,
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
        ]);

        $response = $this->actingAs($this->seller)
            ->post(route('seller.payments.processVerify', $payment), [
                'status' => 'VALID',
            ]);

        $response->assertRedirect(route('seller.invoices.index'))
            ->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(PaymentVerificationStatus::VALID, $payment->status_verifikasi);

        $this->invoice->refresh();
        $this->assertEquals('DIBAYAR', $this->invoice->status);

        $this->purchaseOrder->refresh();
        $this->assertEquals('DIBAYAR', $this->purchaseOrder->status);

        // Riwayat status order tercatat
        $this->assertDatabaseHas('order_status_history', [
            'purchase_orders_id_purchase_order' => $this->purchaseOrder->id_purchase_order,
            'ke' => 'DIBAYAR',
        ]);

        // Notifikasi ke Buyer tercatat
        $this->assertDatabaseHas('notifications', [
            'users_id_user' => $this->buyer->id_user,
            'jenis' => 'pembayaran.diverifikasi',
        ]);
    }

    /**
     * AC 6: Seller dapat menolak pembayaran dengan alasan penolakan.
     */
    public function test_seller_dapat_menolak_pembayaran_dengan_alasan(): void
    {
        $payment = Payment::factory()->create([
            'invoices_id_invoice' => $this->invoice->id_invoice,
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
        ]);

        $response = $this->actingAs($this->seller)
            ->post(route('seller.payments.processVerify', $payment), [
                'status' => 'DITOLAK',
                'alasan_tolak' => 'Bukti mutasi bank tidak sesuai dan nama pengirim berbeda.',
            ]);

        $response->assertRedirect(route('seller.invoices.index'))
            ->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(PaymentVerificationStatus::DITOLAK, $payment->status_verifikasi);
        $this->assertEquals('Bukti mutasi bank tidak sesuai dan nama pengirim berbeda.', $payment->alasan_tolak);

        // Order PO tetap INVOICED
        $this->purchaseOrder->refresh();
        $this->assertEquals('INVOICED', $this->purchaseOrder->status);

        // Notifikasi penolakan ke Buyer
        $this->assertDatabaseHas('notifications', [
            'users_id_user' => $this->buyer->id_user,
            'jenis' => 'pembayaran.ditolak',
        ]);
    }

    /**
     * AC 6: Penolakan gagal jika alasan tolak tidak diisi.
     */
    public function test_penolakan_pembayaran_gagal_jika_alasan_tolak_kosong(): void
    {
        $payment = Payment::factory()->create([
            'invoices_id_invoice' => $this->invoice->id_invoice,
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
        ]);

        $response = $this->actingAs($this->seller)
            ->post(route('seller.payments.processVerify', $payment), [
                'status' => 'DITOLAK',
                'alasan_tolak' => '',
            ]);

        $response->assertSessionHasErrors('alasan_tolak');
    }

    /**
     * AC 7: Buyer perusahaan lain tidak boleh mengakses formulir bayar invoice milik pihak lain (403).
     */
    public function test_buyer_perusahaan_lain_tidak_boleh_unggah_bukti_ke_invoice_pihak_lain(): void
    {
        $otherBuyerCompany = Company::factory()->buyer()->create();
        $otherBuyer = User::factory()->buyer($otherBuyerCompany)->create();

        $response = $this->actingAs($otherBuyer)
            ->get(route('buyer.invoices.pay', $this->invoice));

        $response->assertForbidden();
    }

    /**
     * AC 7: Seller perusahaan lain tidak boleh memverifikasi pembayaran pihak lain (403).
     */
    public function test_seller_perusahaan_lain_tidak_boleh_verifikasi_pembayaran_pihak_lain(): void
    {
        $otherSellerCompany = Company::factory()->seller()->create();
        $otherSeller = User::factory()->seller($otherSellerCompany)->create();

        $payment = Payment::factory()->create([
            'invoices_id_invoice' => $this->invoice->id_invoice,
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
        ]);

        $response = $this->actingAs($otherSeller)
            ->get(route('seller.payments.verify', $payment));

        $response->assertForbidden();

        $responseAction = $this->actingAs($otherSeller)
            ->post(route('seller.payments.processVerify', $payment), [
                'status' => 'VALID',
            ]);

        $responseAction->assertForbidden();
    }

    /**
     * AC 7: Akses pratinjau/unduh berkas bukti transfer dilindungi policy.
     */
    public function test_pengguna_tanpa_hak_akses_tidak_bisa_unduh_berkas_bukti_transfer(): void
    {
        $fakeFile = UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg');
        $path = Storage::disk('local')->putFile('payments', $fakeFile);

        $payment = Payment::factory()->create([
            'invoices_id_invoice' => $this->invoice->id_invoice,
            'bukti_url' => $path,
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
        ]);

        $unauthorizedUser = User::factory()->inspector()->create();

        // Inspector tidak berhak melihat bukti transfer pembayaran
        $response = $this->actingAs($unauthorizedUser)
            ->get(route('payments.proof', $payment));

        $response->assertForbidden();

        // Pihak Buyer pemilik order BERHAK melihat
        $responseBuyer = $this->actingAs($this->buyer)
            ->get(route('payments.proof', $payment));

        $responseBuyer->assertOk();

        // Pihak Seller pemilik order BERHAK melihat
        $responseSeller = $this->actingAs($this->seller)
            ->get(route('payments.proof', $payment));

        $responseSeller->assertOk();
    }
}
