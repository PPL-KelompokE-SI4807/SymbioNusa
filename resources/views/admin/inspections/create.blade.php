@extends('layouts.app')

@section('title', 'Penugasan Inspeksi · PO #' . $order->id_purchase_order)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('admin.inspections.index') }}" class="hover:underline">Penugasan Inspeksi</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">PO #{{ $order->id_purchase_order }}</span>
@endsection

@section('content')
    <div class="space-y-6" x-data="{
        mode: '{{ old('mode_verifikasi', $recommendation['mode']) }}',
        defaultMode: '{{ $recommendation['mode'] }}',
        isOverride: {{ old('is_override', old('mode_verifikasi') && old('mode_verifikasi') !== $recommendation['mode'] ? 'true' : 'false') }},
        selectedInspector: '{{ old('users_id_user') }}'
    }">
        <x-ui.page-header
            title="Form Penugasan Inspeksi"
            subtitle="Pilih verifikator kompeten dan tetapkan jadwal serta mode verifikasi."
        />

        @if ($errors->any())
            <x-ui.alert variant="danger">
                <p class="font-semibold">Mohon perbaiki kesalahan berikut:</p>
                <ul class="mt-1 list-inside list-disc space-y-1 text-body-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('admin.inspections.store', $order) }}" class="space-y-6">
            @csrf
            <input type="hidden" name="is_override" :value="isOverride ? '1' : '0'">

            {{-- 1. Kartu Atas: Data Order & Rekomendasi Mode --}}
            <x-ui.card title="Data Transaksi & Rekomendasi Mode">
                @php
                    $category = $order->rfqContract?->listing?->category;
                @endphp
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Detail Order --}}
                    <div class="space-y-3 rounded-md border border-outline bg-background/50 p-4">
                        <div class="flex items-center justify-between border-b border-outline pb-2">
                            <span class="text-body-sm text-on-surface-muted">Nomor PO</span>
                            <span class="font-semibold text-on-surface">PO #{{ $order->id_purchase_order }}</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-outline pb-2">
                            <span class="text-body-sm text-on-surface-muted">Perusahaan Seller</span>
                            <span class="font-medium text-on-surface">{{ $order->seller?->nama ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-outline pb-2">
                            <span class="text-body-sm text-on-surface-muted">Perusahaan Buyer</span>
                            <span class="font-medium text-on-surface">{{ $order->buyer?->nama ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-outline pb-2">
                            <span class="text-body-sm text-on-surface-muted">Kategori Material</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-medium text-on-surface">{{ $category?->nama ?? 'Umum' }}</span>
                                @if ($category?->tingkat_risiko)
                                    <x-ui.badge :variant="\App\Support\StatusMap::badge('categories.tingkat_risiko', $category->tingkat_risiko)">
                                        {{ \App\Support\StatusMap::label('categories.tingkat_risiko', $category->tingkat_risiko) }}
                                    </x-ui.badge>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-body-sm text-on-surface-muted">Volume Transaksi</span>
                            <span class="font-semibold text-on-surface">{{ \App\Support\Format::kg($order->volume_kg) }}</span>
                        </div>
                    </div>

                    {{-- Kotak Rekomendasi Sistem --}}
                    <div class="space-y-4 rounded-md border border-primary/30 bg-primary-container/20 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-label-sm font-semibold uppercase text-primary">Rekomendasi Sistem (Advisor)</span>
                            <x-ui.badge variant="{{ $recommendation['mode'] === 'DESK' ? 'info' : 'warning' }}">
                                Mode {{ $recommendation['mode'] }}
                            </x-ui.badge>
                        </div>

                        <p class="text-body-md text-on-surface">
                            {{ $recommendation['alasan'] }}
                        </p>

                        {{-- Mode Selector / Override --}}
                        <div class="pt-2 border-t border-primary/20 space-y-3">
                            <label class="block text-label-md font-semibold text-on-surface">
                                Tetapkan Mode Verifikasi:
                            </label>

                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="flex cursor-pointer items-center justify-center rounded-sm border p-3 text-center text-label-md transition-all"
                                    :class="mode === 'DESK'
                                        ? 'border-primary bg-primary text-on-primary font-semibold shadow-sm'
                                        : 'border-outline bg-surface text-on-surface hover:bg-background'"
                                >
                                    <input
                                        type="radio"
                                        name="mode_verifikasi"
                                        value="DESK"
                                        x-model="mode"
                                        @change="isOverride = (mode !== defaultMode)"
                                        class="sr-only"
                                    >
                                    <span>Mode Desk (Digital)</span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-center justify-center rounded-sm border p-3 text-center text-label-md transition-all"
                                    :class="mode === 'LAPANGAN'
                                        ? 'border-primary bg-primary text-on-primary font-semibold shadow-sm'
                                        : 'border-outline bg-surface text-on-surface hover:bg-background'"
                                >
                                    <input
                                        type="radio"
                                        name="mode_verifikasi"
                                        value="LAPANGAN"
                                        x-model="mode"
                                        @change="isOverride = (mode !== defaultMode)"
                                        class="sr-only"
                                    >
                                    <span>Mode Lapangan (Fisik)</span>
                                </label>
                            </div>

                            {{-- Form Alasan Override --}}
                            <div x-show="isOverride" x-cloak class="pt-2 space-y-1.5">
                                <x-ui.field
                                    label="Alasan Perubahan Mode"
                                    for="alasan_mode"
                                    name="alasan_mode"
                                    helper="Wajib diisi jika mode verifikasi diubah dari rekomendasi sistem."
                                    required
                                >
                                    <x-ui.input
                                        id="alasan_mode"
                                        name="alasan_mode"
                                        placeholder="Contoh: Permintaan khusus Buyer untuk inspeksi fisik langsung."
                                        :value="old('alasan_mode')"
                                    />
                                </x-ui.field>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- 2. Kartu Tengah: Daftar Inspector yang Memenuhi Syarat --}}
            <x-ui.card title="Pilih Inspector yang Memenuhi Syarat" :padding="false">
                <div class="px-6 py-3 border-b border-outline bg-background/50">
                    <p class="text-body-sm text-on-surface-muted">
                        Daftar di bawah telah disaring berdasarkan keahlian kategori material, status sertifikasi aktif, dan wilayah tugas.
                    </p>
                </div>

                <x-ui.table label="Daftar Inspector yang Memenuhi Syarat">
                    <x-slot:head>
                        <th scope="col" class="w-12 px-6 py-3 text-center">Pilih</th>
                        <th scope="col" class="px-6 py-3">Nama Inspector</th>
                        <th scope="col" class="px-6 py-3">Bidang Keahlian</th>
                        <th scope="col" class="px-6 py-3">Wilayah Tugas</th>
                        <th scope="col" class="px-6 py-3 text-center">Beban Aktif</th>
                        <th scope="col" class="px-6 py-3 text-right">Skor Kecocokan</th>
                    </x-slot:head>

                    @forelse ($inspectors as $insp)
                        @php
                            $profile = $insp->inspectorProfile;
                            $skills = (array) ($profile?->bidang_keahlian ?? []);
                        @endphp
                        <tr
                            class="border-t border-outline transition-colors hover:bg-background/40 cursor-pointer"
                            @click="selectedInspector = '{{ $insp->id_user }}'"
                            :class="selectedInspector == '{{ $insp->id_user }}' ? 'bg-primary-container/20' : ''"
                        >
                            <td class="px-6 py-4 text-center">
                                <input
                                    type="radio"
                                    name="users_id_user"
                                    value="{{ $insp->id_user }}"
                                    x-model="selectedInspector"
                                    required
                                    class="size-4 text-primary focus:ring-primary"
                                >
                            </td>
                            <td class="px-6 py-4 font-medium text-on-surface">
                                {{ $insp->nama }}
                                <span class="block text-body-xs text-on-surface-muted">No. Sert: {{ $profile?->no_sertifikat ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if ($insp->has_category_match)
                                        <x-ui.badge variant="success">Kategori Sesuai</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral">Kategori Lain</x-ui.badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-body-sm text-on-surface">
                                {{ $profile?->wilayah_tugas ?? '-' }}
                                @if ($insp->has_region_match)
                                    <span class="inline-block ml-1">
                                        <x-ui.badge variant="success">Wilayah Cocok</x-ui.badge>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badgeVar = match (true) {
                                        $insp->active_workload === 0 => 'success',
                                        $insp->active_workload <= 2 => 'info',
                                        default => 'warning',
                                    };
                                @endphp
                                <x-ui.badge :variant="$badgeVar">
                                    {{ $insp->active_workload }} Tugas
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-label-md {{ $insp->match_score >= 70 ? 'text-success' : 'text-primary' }}">
                                    {{ $insp->match_score }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-on-surface-muted">
                                <x-ui.icon name="alert" class="mx-auto size-8 text-warning" />
                                <p class="mt-2 text-body-md font-medium">Tidak ada Inspector aktif yang memenuhi syarat.</p>
                                <p class="text-body-sm">Pastikan terdapat Inspector dengan sertifikat aktif yang memiliki bidang keahlian kategori ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </x-ui.card>

            {{-- 3. Kartu Bawah: Jadwal & Kalender Beban --}}
            <x-ui.card title="Jadwal Pelaksanaan & Konfirmasi">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-3">
                        <x-ui.field
                            label="Tanggal & Jam Inspeksi"
                            for="jadwal"
                            name="jadwal"
                            helper="Tentukan waktu pelaksanaan inspeksi. Sistem akan memvalidasi potensi bentrok jadwal."
                            required
                        >
                            <x-ui.input
                                id="jadwal"
                                name="jadwal"
                                type="datetime-local"
                                :value="old('jadwal', now()->addDays(2)->setHour(10)->setMinute(0)->format('Y-m-d\TH:i'))"
                                required
                            />
                        </x-ui.field>
                    </div>

                    <div class="rounded-md border border-outline bg-background/50 p-4 space-y-2">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="info" class="size-4 text-primary" />
                            <span class="text-label-sm font-semibold text-on-surface">Pencegahan Bentrok Beban</span>
                        </div>
                        <p class="text-body-sm text-on-surface-muted">
                            Sistem secara otomatis mengevaluasi jadwal pada tanggal yang dipilih. Jika Inspector telah memiliki penugasan lain pada tanggal yang sama, sistem akan meminta konfirmasi atau penjadwalan ulang untuk menjaga kualitas verifikasi.
                        </p>
                    </div>
                </div>

                {{-- Tombol Aksi Bawah --}}
                <div class="mt-8 flex items-center justify-between border-t border-outline pt-6">
                    <x-ui.button
                        variant="secondary"
                        href="{{ route('admin.inspections.index') }}"
                    >
                        Batal
                    </x-ui.button>

                    <x-ui.button
                        variant="primary"
                        type="submit"
                        :disabled="$inspectors->isEmpty()"
                    >
                        Tugaskan Inspector
                    </x-ui.button>
                </div>
            </x-ui.card>
        </form>
    </div>
@endsection
