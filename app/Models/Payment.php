<?php

namespace App\Models;

use App\Enums\PaymentVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk tabel `payments` sesuai skema DDL.
 *
 * @property int $id_payment
 * @property string|null $bukti_url
 * @property \Illuminate\Support\Carbon|null $tgl_transfer
 * @property string $nominal
 * @property string|null $rekening_asal
 * @property PaymentVerificationStatus|string $status_verifikasi
 * @property string|null $alasan_tolak
 * @property int $invoices_id_invoice
 */
class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $primaryKey = 'id_payment';

    public $timestamps = false;

    protected $fillable = [
        'id_payment',
        'bukti_url',
        'tgl_transfer',
        'nominal',
        'rekening_asal',
        'status_verifikasi',
        'alasan_tolak',
        'invoices_id_invoice',
    ];

    protected function casts(): array
    {
        return [
            'tgl_transfer' => 'date',
            'nominal' => 'decimal:2',
            'status_verifikasi' => PaymentVerificationStatus::class,
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoices_id_invoice', 'id_invoice');
    }
}
