<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * FND-03 (SYM-3): permintaan tautan reset kata sandi lewat email.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Pesan yang sama untuk semua kasus, supaya halaman ini tidak bisa dipakai menebak email terdaftar.
     */
    public const GENERIC_MESSAGE = 'Jika email terdaftar dan akunnya aktif, tautan untuk mengatur ulang kata sandi sudah dikirim. Periksa kotak masuk Anda.';

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = $request->string('email')->lower()->toString();

        // Hanya akun AKTIF yang dikirimi tautan (selaras dengan aturan login FND-01).
        $isActive = User::query()->where('email', $email)->where('status', 'AKTIF')->exists();

        if ($isActive) {
            // Broker bawaan Laravel membatasi pengiriman ulang per akun (throttle 60 detik).
            Password::sendResetLink(['email' => $email]);
        }

        return back()->with('success', self::GENERIC_MESSAGE);
    }
}
