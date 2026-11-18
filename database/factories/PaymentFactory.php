<?php

namespace Database\Factories;

use App\Enums\PaymentVerificationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'bukti_url' => 'payments/bukti_'.fake()->uuid().'.jpg',
            'tgl_transfer' => now()->toDateString(),
            'nominal' => 15000000.00,
            'rekening_asal' => 'BCA 1234567890 a.n. '.fake('id_ID')->name(),
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
            'alasan_tolak' => null,
            'invoices_id_invoice' => Invoice::factory(),
        ];
    }

    public function menunggu(): static
    {
        return $this->state(fn () => [
            'status_verifikasi' => PaymentVerificationStatus::MENUNGGU,
            'alasan_tolak' => null,
        ]);
    }

    public function valid(): static
    {
        return $this->state(fn () => [
            'status_verifikasi' => PaymentVerificationStatus::VALID,
            'alasan_tolak' => null,
        ]);
    }

    public function ditolak(?string $alasan = 'Bukti transfer tidak terbaca / buram'): static
    {
        return $this->state(fn () => [
            'status_verifikasi' => PaymentVerificationStatus::DITOLAK,
            'alasan_tolak' => $alasan,
        ]);
    }
}
