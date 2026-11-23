<div>
    @php
        // Judul menamai konteks (organisasi atau orang), bukan sapaan (DESIGN.md › Typography, amandemen FND v8).
        $now = \Illuminate\Support\Carbon::now('Asia/Jakarta');
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $stamp = 'per '.$now->format('j').' '.$months[$now->month - 1].' '.$now->format('Y, H.i').' WIB';
        $roleNames = ['ADMIN' => 'Admin', 'SELLER' => 'Seller', 'BUYER' => 'Buyer', 'INSPECTOR' => 'Inspector'];
        $title = match ($role) {
            'ADMIN' => 'Dinas Lingkungan Hidup',
            'SELLER', 'BUYER' => $user->company->nama ?? $user->nama,
            default => $user->nama,
        };
        $subtitle = 'Dashboard '.($roleNames[$role] ?? 'Pengguna').' · '.$stamp;
    @endphp

    <x-ui.page-header :title="$title" :subtitle="$subtitle">
        @if ($role === 'BUYER' && Route::has('buyer.demands.create') && $user->id_company)
            <x-slot:actions>
                <x-ui.button size="lg" :href="route('buyer.demands.create')">
                    <x-ui.icon name="plus" class="size-[18px]" />
                    Ajukan kebutuhan
                </x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($failed)
        <x-ui.alert variant="warning" class="mb-6">
            Sebagian data dasbor gagal dimuat. Muat ulang halaman; bila masih gagal, hubungi Admin Platform.
        </x-ui.alert>
    @endif

    @if (in_array($role, ['SELLER', 'BUYER'], true) && ! $user->id_company)
        <x-ui.alert variant="info" title="Akun belum terhubung ke perusahaan">
            Lengkapi profil perusahaan agar listing, kebutuhan, dan transaksi Anda tampil di sini.
        </x-ui.alert>
    @elseif ($data !== [])
        @include('livewire.dashboard.'.strtolower($role), $data)
    @endif
</div>
