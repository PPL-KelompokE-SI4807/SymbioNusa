<?php

namespace App\Livewire;

use App\Services\Dashboard\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * FND-04 (SYM-28): dasbor utama per peran. Data dari DashboardService; tampilan per peran di
 * resources/views/livewire/dashboard/{admin,seller,buyer,inspector}.blade.php.
 */
class DashboardOverview extends Component
{
    public function render(DashboardService $service): View
    {
        $user = Auth::user();
        $role = strtoupper($user->role ?? '');
        $data = [];
        $failed = false;

        try {
            $data = match ($role) {
                'ADMIN' => $service->admin(),
                'SELLER' => $user->id_company ? $service->seller((int) $user->id_company) : [],
                'BUYER' => $user->id_company ? $service->buyer((int) $user->id_company) : [],
                'INSPECTOR' => $service->inspector((int) $user->id_user),
                default => [],
            };
        } catch (\Throwable $e) {
            // Detail hanya ke log; pengguna mendapat pesan umum (lihat docs/design/prompts/04).
            Log::error('Dasbor gagal dimuat', ['exception' => $e, 'role' => $role, 'user_id' => $user?->id_user]);
            $failed = true;
        }

        return view('livewire.dashboard-overview', [
            'user' => $user,
            'role' => $role,
            'data' => $data,
            'failed' => $failed,
        ]);
    }
}
