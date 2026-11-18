<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');
        if (! $invoice instanceof Invoice) {
            $invoiceId = $this->input('invoices_id_invoice');
            $invoice = Invoice::find($invoiceId);
        }

        if (! $invoice instanceof Invoice) {
            return false;
        }

        return $this->user()?->can('create', [Payment::class, $invoice]) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxMb = (int) config('symbionusa.max_upload_size_mb', 5);
        $maxKb = $maxMb * 1024;

        return [
            'tgl_transfer' => ['required', 'date'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'bank_asal' => ['required', 'string', 'max:50'],
            'nomor_rekening_asal' => ['required', 'string', 'max:50'],
            'nama_rekening_asal' => ['required', 'string', 'max:100'],
            'bukti' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', "max:{$maxKb}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tgl_transfer.required' => 'Tanggal transfer wajib diisi.',
            'tgl_transfer.date' => 'Format tanggal transfer tidak valid.',
            'nominal.required' => 'Nominal transfer wajib diisi.',
            'nominal.numeric' => 'Nominal transfer harus berupa angka.',
            'nominal.min' => 'Nominal transfer tidak boleh kurang dari 0.',
            'bank_asal.required' => 'Nama bank asal wajib diisi.',
            'nomor_rekening_asal.required' => 'Nomor rekening asal wajib diisi.',
            'nama_rekening_asal.required' => 'Nama pemilik rekening asal wajib diisi.',
            'bukti.required' => 'Berkas bukti transfer wajib diunggah.',
            'bukti.file' => 'Bukti transfer harus berupa berkas.',
            'bukti.mimes' => 'Bukti transfer harus berformat PDF, JPG, JPEG, atau PNG.',
            'bukti.max' => 'Ukuran berkas bukti transfer maksimal 5 MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $invoice = $this->route('invoice');
            if (! $invoice instanceof Invoice) {
                $invoiceId = $this->input('invoices_id_invoice');
                $invoice = Invoice::find($invoiceId);
            }

            if (! $invoice instanceof Invoice) {
                return;
            }

            $nominal = $this->input('nominal');
            if ($nominal !== null && is_numeric($nominal)) {
                $nominalFloat = round((float) $nominal, 2);
                $totalFloat = round((float) $invoice->total, 2);

                if (abs($nominalFloat - $totalFloat) > 0.01) {
                    $formattedTotal = number_format($totalFloat, 0, ',', '.');
                    $validator->errors()->add(
                        'nominal',
                        "Nominal transfer (Rp " . number_format($nominalFloat, 0, ',', '.') . ") harus sama persis dengan total tagihan invoice (Rp {$formattedTotal})."
                    );
                }
            }
        });
    }
}
