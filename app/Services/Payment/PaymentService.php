<?php

namespace App\Services\Payment;

use App\Enums\PaymentVerificationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Menyimpan unggahan bukti pembayaran baru dari Buyer.
     *
     * @param  Invoice  $invoice
     * @param  array<string, mixed>  $data
     * @param  UploadedFile  $file
     * @param  User|null  $actor
     * @return Payment
     */
    public function createPayment(Invoice $invoice, array $data, UploadedFile $file, ?User $actor = null): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $file, $actor) {
            // Simpan berkas bukti transfer ke disk privat 'local'
            $path = Storage::disk('local')->putFile('payments', $file);
            if (! $path) {
                throw new InvalidArgumentException('Gagal menyimpan berkas bukti transfer.');
            }

            $rekeningAsal = trim(sprintf(
                '%s %s a.n. %s',
                $data['bank_asal'] ?? '',
                $data['nomor_rekening_asal'] ?? '',
                $data['nama_rekening_asal'] ?? ''
            ));

            $payment = Payment::create([
                'bukti_url' => $path,
                'tgl_transfer' => $data['tgl_transfer'],
                'nominal' => $data['nominal'],
                'rekening_asal' => $rekeningAsal,
                'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
                'alasan_tolak' => null,
                'invoices_id_invoice' => $invoice->id_invoice,
            ]);

            // Kirim notifikasi ke Seller (pemilik invoice)
            $sellerCompanyId = $invoice->purchaseOrder?->companies_id_seller;
            if ($sellerCompanyId) {
                $sellerUser = User::where('id_company', $sellerCompanyId)
                    ->where('role', 'SELLER')
                    ->first();

                if ($sellerUser) {
                    $this->notificationService->send(
                        $sellerUser->id_user,
                        'pembayaran.diunggah',
                        [
                            'pesan' => "Bukti pembayaran untuk Invoice {$invoice->nomor} telah diunggah oleh Buyer dan menunggu verifikasi.",
                            'id_invoice' => $invoice->id_invoice,
                            'id_payment' => $payment->id_payment,
                            'nominal' => (float) $payment->nominal,
                        ]
                    );
                }
            }

            return $payment;
        });
    }

    /**
     * Memverifikasi bukti pembayaran (VALID atau DITOLAK) oleh Seller.
     *
     * @param  Payment  $payment
     * @param  string  $status  'VALID' atau 'DITOLAK'
     * @param  string|null  $alasanTolak
     * @param  User|null  $actor
     * @return Payment
     */
    public function verifyPayment(Payment $payment, string $status, ?string $alasanTolak = null, ?User $actor = null): Payment
    {
        return DB::transaction(function () use ($payment, $status, $alasanTolak, $actor) {
            $payment->status_verifikasi = $status;
            $payment->alasan_tolak = ($status === 'DITOLAK') ? $alasanTolak : null;
            $payment->save();

            $invoice = $payment->invoice;
            $po = $invoice?->purchaseOrder;

            $buyerCompanyId = $po?->companies_id_buyer;
            $buyerUser = $buyerCompanyId ? User::where('id_company', $buyerCompanyId)
                ->where('role', 'BUYER')
                ->first() : null;

            if ($status === 'VALID') {
                if ($invoice) {
                    $invoice->status = 'DIBAYAR';
                    $invoice->save();
                }

                if ($po) {
                    $dariStatus = $po->status;
                    // TODO(SYM-5/SYM-9): panggil OrderStateMachine setelah tersedia oleh Dhafi
                    $po->status = 'DIBAYAR';
                    $po->save();

                    // Catat ke riwayat status order sesuai DDL order_status_history
                    DB::table('order_status_history')->insert([
                        'dari' => $dariStatus,
                        'ke' => 'DIBAYAR',
                        'alasan' => 'Bukti pembayaran transfer telah diverifikasi valid oleh Seller.',
                        'waktu' => now(),
                        'purchase_orders_id_purchase_order' => $po->id_purchase_order,
                        'users_id_user' => $actor?->id_user,
                    ]);
                }

                if ($buyerUser && $invoice) {
                    $this->notificationService->send(
                        $buyerUser->id_user,
                        'pembayaran.diverifikasi',
                        [
                            'pesan' => "Pembayaran untuk Invoice {$invoice->nomor} telah diverifikasi oleh Seller. Status order kini DIBAYAR.",
                            'id_invoice' => $invoice->id_invoice,
                            'id_payment' => $payment->id_payment,
                            'status' => 'VALID',
                        ]
                    );
                }
            } else {
                // Status DITOLAK
                if ($buyerUser && $invoice) {
                    $this->notificationService->send(
                        $buyerUser->id_user,
                        'pembayaran.ditolak',
                        [
                            'pesan' => "Pembayaran untuk Invoice {$invoice->nomor} ditolak oleh Seller. Alasan: {$alasanTolak}",
                            'id_invoice' => $invoice->id_invoice,
                            'id_payment' => $payment->id_payment,
                            'status' => 'DITOLAK',
                            'alasan_tolak' => $alasanTolak,
                        ]
                    );
                }
            }

            return $payment;
        });
    }

    /**
     * Mendapatkan path absolut berkas bukti pembayaran di storage disk 'local'.
     */
    public function getProofAbsolutePath(Payment $payment): ?string
    {
        if (! $payment->bukti_url) {
            return null;
        }

        if (Storage::disk('local')->exists($payment->bukti_url)) {
            return Storage::disk('local')->path($payment->bukti_url);
        }

        return null;
    }
}
