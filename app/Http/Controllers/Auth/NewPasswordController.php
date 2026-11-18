<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * FND-03 (SYM-3): formulir dan penyimpanan kata sandi baru dari tautan reset.
 */
class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            [
                'email' => $request->string('email')->lower()->toString(),
                'password' => $request->string('password')->toString(),
                'password_confirmation' => $request->string('password_confirmation')->toString(),
                'token' => $request->string('token')->toString(),
            ],
            function (User $user, string $password): void {
                // Cast 'hashed' pada model User meng-hash nilai ini sebelum disimpan ke password_hash.
                $user->forceFill(['password_hash' => $password])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Minta tautan baru.']);
    }
}
