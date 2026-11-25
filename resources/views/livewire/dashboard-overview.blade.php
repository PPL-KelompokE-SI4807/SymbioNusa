{{-- Varian Senja di Sawah (.dash-senja) untuk keempat dashboard peran (panduan dashboard Senja). --}}
<div @class(['dash-senja' => in_array($role, ['ADMIN', 'SELLER', 'BUYER', 'INSPECTOR'], true), 'dash-kaca' => $role === 'ADMIN'])
     @if ($role === 'ADMIN')
         {{-- Kilau kaca mengikuti kursor: hanya menulis --mx/--my pada kartu di bawah kursor (CSS menyembunyikannya saat gerak tereduksi). --}}
         x-data
         @pointermove.passive="const c = $event.target.closest('[data-card]'); if (c) { const r = c.getBoundingClientRect(); c.style.setProperty('--mx', ($event.clientX - r.left) + 'px'); c.style.setProperty('--my', ($event.clientY - r.top) + 'px'); }"
     @endif>
    @if ($role === 'ADMIN')
        {{-- Varian Kaca (DESIGN.md, amandemen FND v12): foto material diburamkan di belakang kartu kaca. Hiasan murni, tanpa data. --}}
        <div class="kaca-backdrop" aria-hidden="true">
            <div class="kaca-blob" style="background-image: url('{{ asset('images/material/kain.jpg') }}'); width: 46vw; height: 52vh; left: -6vw; top: -8vh"></div>
            <div class="kaca-blob" style="background-image: url('{{ asset('images/material/ampas-tahu.jpg') }}'); width: 40vw; height: 46vh; right: -4vw; top: 10vh"></div>
            <div class="kaca-blob" style="background-image: url('{{ asset('images/material/serbuk-kayu.jpg') }}'); width: 52vw; height: 50vh; left: 22vw; bottom: -14vh"></div>
            <div class="kaca-blob" style="background-image: url('{{ asset('images/material/plastik-2.jpg') }}'); width: 30vw; height: 36vh; right: 2vw; bottom: 4vh"></div>
        </div>
    @endif
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

    @if ($role !== 'ADMIN')
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
    @endif

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
