<?php

namespace App\Policies;

use App\Enums\PaymentVerificationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Memeriksa apakah pengguna berhak melihat daftar invoice/pembayaran miliknya.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['BUYER', 'SELLER', 'ADMIN'], true);
    }

    /**
     * Memeriksa apakah pengguna berhak melihat detail pembayaran dan bukti transfer.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->role === 'ADMIN') {
            return true;
        }

        if (! $user->id_company) {
            return false;
        }

        $po = $payment->invoice?->purchaseOrder;
        if (! $po) {
            return false;
        }

        return $po->companies_id_buyer === $user->id_company
            || $po->companies_id_seller === $user->id_company;
    }

    /**
     * Memeriksa apakah Buyer berhak mengunggah bukti pembayaran untuk invoice tertentu.
     */
    public function create(User $user, Invoice $invoice): bool
    {
        if ($user->role !== 'BUYER' || $user->status !== 'AKTIF' || ! $user->id_company) {
            return false;
        }

        $po = $invoice->purchaseOrder;
        if (! $po || $po->companies_id_buyer !== $user->id_company) {
            return false;
        }

        // Invoice harus berstatus TERBIT (belum lunas dan tidak void)
        if ($invoice->status !== 'TERBIT') {
            return false;
        }

        // Jika sudah ada pembayaran valid, tidak boleh unggah lagi
        $hasValidPayment = $invoice->payments()
            ->where('status_verifikasi', PaymentVerificationStatus::VALID->value)
            ->exists();

        return ! $hasValidPayment;
    }

    /**
     * Memeriksa apakah Buyer berhak mengganti bukti pembayaran yang belum diverifikasi atau ditolak.
     */
    public function update(User $user, Payment $payment): bool
    {
        if ($user->role !== 'BUYER' || $user->status !== 'AKTIF' || ! $user->id_company) {
            return false;
        }

        $po = $payment->invoice?->purchaseOrder;
        if (! $po || $po->companies_id_buyer !== $user->id_company) {
            return false;
        }

        // Bukti hanya dapat diganti sebelum diverifikasi valid (status MENUNGGU atau DITOLAK)
        return $payment->status_verifikasi !== PaymentVerificationStatus::VALID;
    }

    /**
     * Memeriksa apakah Seller berhak memverifikasi atau menolak bukti pembayaran.
     */
    public function verify(User $user, Payment $payment): bool
    {
        if ($user->role !== 'SELLER' || $user->status !== 'AKTIF' || ! $user->id_company) {
            return false;
        }

        $po = $payment->invoice?->purchaseOrder;
        if (! $po || $po->companies_id_seller !== $user->id_company) {
            return false;
        }

        // Hanya pembayaran berstatus MENUNGGU yang bisa diverifikasi/ditolak
        return $payment->status_verifikasi === PaymentVerificationStatus::MENUNGGU;
    }
}
