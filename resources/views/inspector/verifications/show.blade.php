@extends('layouts.app')

@section('title', 'Verifikasi Dokumen · PO #' . $verification->purchase_orders_id_purchase_order)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <a href="{{ route('inspector.verifications.index') }}" class="hover:underline">Verifikasi Dokumen</a>
    <span class="mx-2 text-on-surface-muted" aria-hidden="true">/</span>
    <span class="text-on-surface" aria-current="page">PO #{{ $verification->purchase_orders_id_purchase_order }}</span>
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Verifikasi Dokumen"
            subtitle="Periksa dokumen perusahaan dan bukti listing."
        />

        @if (session('success'))
            <x-ui.alert variant="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="danger">
                <p class="font-semibold">Mohon periksa kembali isian form verifikasi:</p>
                <ul class="mt-1 list-inside list-disc space-y-1 text-body-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- Context Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-md border border-outline bg-surface px-6 py-4">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-container text-label-md font-semibold text-on-primary-container">
                    {{ mb_substr($verification->purchaseOrder?->seller?->nama ?? 'P', 0, 1) }}
                </span>
                <div>
                    <h2 class="text-label-lg font-semibold text-on-surface">
                        {{ $verification->purchaseOrder?->seller?->nama ?? 'Perusahaan' }}
                    </h2>
                    <p class="text-body-sm text-on-surface-muted">
                        Inspeksi PO #{{ $verification->purchase_orders_id_purchase_order }} · Buyer: {{ $verification->purchaseOrder?->buyer?->nama ?? '-' }}
                    </p>
                </div>
            </div>
            <div>
                <x-ui.badge :variant="\App\Support\StatusMap::badge('document_verifications.status', $verification->status)">
                    {{ \App\Support\StatusMap::label('document_verifications.status', $verification->status) }}
                </x-ui.badge>
            </div>
        </div>

        {{-- Grid Konten Utama --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12" x-data="{
            activeTab: 'legalitas',
            previewUrl: '',
            previewTitle: ''
        }">
            {{-- Kartu Kiri: Pratinjau Dokumen (8 Kolom di LG) --}}
            <div class="space-y-4 lg:col-span-8">
                <x-ui.card :padding="false">
                    {{-- Navigasi Tab --}}
                    <div class="border-b border-outline px-6 pt-4">
                        <nav class="-mb-px flex space-x-6" aria-label="Tab Dokumen">
                            <button
                                type="button"
                                @click="activeTab = 'legalitas'"
                                :class="activeTab === 'legalitas'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-on-surface-muted hover:text-on-surface hover:border-outline'"
                                class="whitespace-nowrap border-b-2 py-3 text-label-md transition-colors"
                            >
                                Dokumen Legalitas ({{ count($documents['legalitas']) }})
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'izin_usaha'"
                                :class="activeTab === 'izin_usaha'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-on-surface-muted hover:text-on-surface hover:border-outline'"
                                class="whitespace-nowrap border-b-2 py-3 text-label-md transition-colors"
                            >
                                Izin Usaha & Lingkungan ({{ count($documents['izin_usaha']) }})
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'bukti_listing'"
                                :class="activeTab === 'bukti_listing'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-on-surface-muted hover:text-on-surface hover:border-outline'"
                                class="whitespace-nowrap border-b-2 py-3 text-label-md transition-colors"
                            >
                                Bukti Listing ({{ count($documents['bukti_listing']) }})
                            </button>
                        </nav>
                    </div>

                    {{-- Isi Tab 1: Dokumen Legalitas --}}
                    <div x-show="activeTab === 'legalitas'" class="p-6">
                        @if ($documents['legalitas']->isEmpty())
                            <div class="rounded-md border border-dashed border-outline p-8 text-center text-on-surface-muted">
                                <p class="text-body-md font-medium">Tidak ada dokumen legalitas umum yang terlampir.</p>
                                <p class="text-body-sm mt-1">Perusahaan belum mengunggah dokumen NIB atau Akta Pendirian.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($documents['legalitas'] as $doc)
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-outline bg-background p-4">
                                        <div>
                                            <p class="font-semibold text-on-surface">{{ $doc->jenis }}</p>
                                            <p class="text-body-sm text-on-surface-muted">
                                                Status: <span class="capitalize">{{ strtolower($doc->status) }}</span>
                                                @if ($doc->berlaku_sampai)
                                                    · Berlaku s/d: {{ \App\Support\Format::date($doc->berlaku_sampai) }}
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            @if ($doc->file_url)
                                                <x-ui.button
                                                    variant="secondary"
                                                    type="button"
                                                    @click="previewUrl = '{{ asset($doc->file_url) }}'; previewTitle = '{{ $doc->jenis }}'"
                                                >
                                                    Lihat Dokumen
                                                </x-ui.button>
                                            @else
                                                <span class="text-body-sm text-on-surface-muted italic">Berkas tidak tersedia</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Isi Tab 2: Izin Usaha & Lingkungan --}}
                    <div x-show="activeTab === 'izin_usaha'" class="p-6" style="display: none;">
                        @if ($documents['izin_usaha']->isEmpty())
                            <div class="rounded-md border border-dashed border-outline p-8 text-center text-on-surface-muted">
                                <p class="text-body-md font-medium">Tidak ada izin lingkungan / AMDAL yang terlampir.</p>
                                <p class="text-body-sm mt-1">Perusahaan belum mengunggah izin lingkungan atau standar mutu khusus.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($documents['izin_usaha'] as $doc)
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-outline bg-background p-4">
                                        <div>
                                            <p class="font-semibold text-on-surface">{{ $doc->jenis }}</p>
                                            <p class="text-body-sm text-on-surface-muted">
                                                Status: <span class="capitalize">{{ strtolower($doc->status) }}</span>
                                                @if ($doc->berlaku_sampai)
                                                    · Berlaku s/d: {{ \App\Support\Format::date($doc->berlaku_sampai) }}
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            @if ($doc->file_url)
                                                <x-ui.button
                                                    variant="secondary"
                                                    type="button"
                                                    @click="previewUrl = '{{ asset($doc->file_url) }}'; previewTitle = '{{ $doc->jenis }}'"
                                                >
                                                    Lihat Dokumen
                                                </x-ui.button>
                                            @else
                                                <span class="text-body-sm text-on-surface-muted italic">Berkas tidak tersedia</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Isi Tab 3: Bukti Listing --}}
                    <div x-show="activeTab === 'bukti_listing'" class="p-6" style="display: none;">
                        @if ($documents['bukti_listing']->isEmpty())
                            <div class="rounded-md border border-dashed border-outline p-8 text-center text-on-surface-muted">
                                <p class="text-body-md font-medium">Tidak ada bukti listing yang terlampir pada penawaran ini.</p>
                                <p class="text-body-sm mt-1">Tidak ada lampiran surat pernyataan non-B3 atau foto sampel yang diunggah.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($documents['bukti_listing'] as $att)
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-outline bg-background p-4">
                                        <div>
                                            <p class="font-semibold text-on-surface">{{ $att->jenis }}</p>
                                            <p class="text-body-sm text-on-surface-muted">
                                                @if ($att->tanggal_pengambilan)
                                                    Tanggal pengambilan: {{ \App\Support\Format::date($att->tanggal_pengambilan) }} ·
                                                @endif
                                                Status: <span class="capitalize">{{ strtolower($att->status ?? 'terlampir') }}</span>
                                            </p>
                                        </div>
                                        <div>
                                            @if ($att->file_url)
                                                <x-ui.button
                                                    variant="secondary"
                                                    type="button"
                                                    @click="previewUrl = '{{ asset($att->file_url) }}'; previewTitle = '{{ $att->jenis }}'"
                                                >
                                                    Lihat Dokumen
                                                </x-ui.button>
                                            @else
                                                <span class="text-body-sm text-on-surface-muted italic">Berkas tidak tersedia</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Area Viewer Pratinjau Dokumen --}}
                    <div class="border-t border-outline bg-background p-6">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-label-md font-semibold text-on-surface">Pratinjau Dokumen</h3>
                            <template x-if="previewTitle">
                                <span class="text-body-sm text-on-surface-muted font-medium" x-text="previewTitle"></span>
                            </template>
                        </div>

                        <div class="flex min-h-[320px] w-full items-center justify-center rounded-sm border border-outline bg-surface p-4">
                            <template x-if="previewUrl">
                                <div class="w-full space-y-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            :href="previewUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 text-body-sm text-primary hover:underline"
                                        >
                                            Buka di tab baru ↗
                                        </a>
                                    </div>
                                    <iframe
                                        :src="previewUrl"
                                        class="h-[480px] w-full rounded border border-outline"
                                        title="Pratinjau Dokumen"
                                    ></iframe>
                                </div>
                            </template>
                            <template x-if="!previewUrl">
                                <div class="text-center text-on-surface-muted py-12">
                                    <x-ui.icon name="file-text" class="mx-auto size-12 opacity-30" />
                                    <p class="mt-2 text-body-md">Pilih salah satu dokumen di atas untuk melihat pratinjau.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- Kartu Kanan: Checklist Verifikasi (4 Kolom di LG) --}}
            <div class="space-y-4 lg:col-span-4">
                <x-ui.card title="Checklist Verifikasi">
                    @php
                        $isPending = $verification->status === \App\Models\DocumentVerification::STATUS_MENUNGGU;
                        $existingChecklist = $verification->checklist ?? [];
                    @endphp

                    <form method="POST" action="{{ route('inspector.verifications.verify', $verification) }}" class="space-y-6">
                        @csrf

                        {{-- Daftar Butir Checklist --}}
                        <div class="space-y-3">
                            <p class="text-label-sm font-semibold uppercase text-on-surface-muted tracking-wider">
                                Kriteria Pemeriksaan
                            </p>

                            @foreach ($checklistItems as $key => $label)
                                @php
                                    $isChecked = old("checklist.{$key}", $existingChecklist[$key] ?? false);
                                @endphp
                                <label class="flex items-start gap-3 rounded-md border border-outline p-3 transition-colors hover:bg-background/40 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="checklist[{{ $key }}]"
                                        value="1"
                                        @checked($isChecked)
                                        @disabled(! $isPending)
                                        class="mt-0.5 size-4 rounded-sm border-outline-input text-primary focus:ring-primary"
                                    >
                                    <span class="text-body-md text-on-surface select-none">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Catatan / Alasan --}}
                        <div class="space-y-1.5 pt-2 border-t border-outline">
                            <x-ui.field
                                label="Catatan"
                                for="alasan"
                                name="alasan"
                                helper="Wajib diisi jika menolak verifikasi dokumen."
                            >
                                <x-ui.textarea
                                    id="alasan"
                                    name="alasan"
                                    rows="4"
                                    placeholder="Tuliskan catatan hasil verifikasi atau alasan penolakan..."
                                    :disabled="! $isPending"
                                >{{ old('alasan', $verification->alasan) }}</x-ui.textarea>
                            </x-ui.field>
                        </div>

                        {{-- Tombol Aksi --}}
                        @if ($isPending)
                            <div class="flex items-center justify-between gap-3 pt-4 border-t border-outline">
                                <x-ui.button
                                    variant="danger"
                                    type="submit"
                                    name="status"
                                    value="{{ \App\Models\DocumentVerification::STATUS_DITOLAK }}"
                                >
                                    Tolak
                                </x-ui.button>

                                <x-ui.button
                                    variant="primary"
                                    type="submit"
                                    name="status"
                                    value="{{ \App\Models\DocumentVerification::STATUS_SETUJU }}"
                                >
                                    Setujui
                                </x-ui.button>
                            </div>
                        @else
                            <div class="rounded-md bg-neutral-container p-4 text-center text-body-sm text-on-neutral-container">
                                Verifikasi dokumen telah diselesaikan dengan status
                                <span class="font-semibold">{{ \App\Support\StatusMap::label('document_verifications.status', $verification->status) }}</span>.
                            </div>
                        @endif

                        <p class="text-center text-body-sm text-on-surface-muted italic">
                            Hanya Inspector yang ditugaskan dapat memutuskan.
                        </p>
                    </form>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection
