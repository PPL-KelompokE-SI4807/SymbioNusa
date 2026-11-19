<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class DemoActor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = null;

        if ($id = $request->session()->get('demo_user_id')) {
            $user = User::find($id);
        }

        if (! $user) {
            $user = User::where('role', User::ROLE_SELLER)->orderBy('id')->first();
            if ($user) {
                $request->session()->put('demo_user_id', $user->id);
            }
        }

        abort_if(! $user, 503, 'Data demo belum ada. Jalankan: php artisan migrate --seed');

        Auth::setUser($user);

        return $next($request);
    }
}
