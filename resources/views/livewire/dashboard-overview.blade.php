<div>
    @php
        $subtitles = [
            'ADMIN' => 'Ringkasan aliran material, dampak, dan antrean tindakan platform.',
            'SELLER' => 'Listing, order masuk, dan tagihan perusahaan Anda.',
            'BUYER' => 'Kebutuhan bahan baku, order, dan tagihan perusahaan Anda.',
            'INSPECTOR' => 'Penugasan inspeksi dan laporan mutu Anda.',
        ];
    @endphp

    <x-ui.page-header title="Dashboard" :subtitle="$subtitles[$role] ?? 'Ringkasan akun Anda.'" />

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
