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
 * Rute ini HANYA didaftarkan saat APP_ENV=local (lihat routes/auth.php),
 * sehingga tidak pernah tersedia di server staging maupun produksi.
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
        abort_unless(app()->environment('local'), 404);

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
