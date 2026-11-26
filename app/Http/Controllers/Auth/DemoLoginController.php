<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Masuk 1-klik ke akun demo hasil seeder.
 *
 * Hanya berjalan bila config('app.demo_login') aktif: default saat APP_ENV=local,
 * atau DEMO_LOGIN=true di .env (misalnya di staging untuk demo). Selain itu 404.
 */
class DemoLoginController extends Controller
{
    /**
     * Peran demo => email akun dari SymbioNusaDemoSeeder.
     *
     * @var array<string, string>
     */
    public const ACCOUNTS = [
        'admin' => 'admin@symbionusa.test',
        'seller' => 'seller@symbionusa.test',
        'buyer' => 'buyer@symbionusa.test',
        'inspector' => 'inspector@symbionusa.test',
    ];

    public function __invoke(Request $request, string $role): RedirectResponse
    {
        abort_unless(config('app.demo_login'), 404);

        $email = self::ACCOUNTS[strtolower($role)] ?? null;
        abort_if($email === null, 404);

        $user = User::query()
            ->where('email', $email)
            ->where('status', 'AKTIF')
            ->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Akun demo belum ada. Jalankan: php artisan db:seed --class=SymbioNusaDemoSeeder');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
