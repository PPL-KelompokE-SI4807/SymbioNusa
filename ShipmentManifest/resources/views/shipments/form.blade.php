@extends('layouts.app')

@php
    $isEdit = $shipment !== null;
    $fmtDt = fn ($v) => $v ? $v->format('Y-m-d\TH:i') : null;
@endphp

@section('title', $isEdit ? 'Ubah Pengiriman' : 'Buat Pengiriman')

@section('content')
    <a href="{{ $isEdit ? route('shipments.show', $shipment) : route('shipments.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800">&larr; Kembali</a>

    <div class="mb-6 mt-2">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $isEdit ? 'Ubah Draf Pengiriman' : 'Buat Pengiriman Baru' }}</h1>
        <p class="mt-1 text-sm text-zinc-500">Pengiriman hanya dapat dibuat untuk order yang sudah dibayar dan dokumen non-B3-nya disetujui.</p>
    </div>

    @if (! $isEdit && $orders->isEmpty())
        <div class="card p-8 text-center">
            <p class="font-medium">Belum ada order yang siap dikirim</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-zinc-500">
                Order muncul di sini setelah berstatus <strong>Dibayar</strong>, verifikasi dokumen legalitas non-B3 <strong>Disetujui</strong>,
                dan belum memiliki pengiriman aktif.
            </p>
            <a href="{{ route('shipments.index') }}" class="btn-secondary mt-5">Kembali ke daftar</a>
        </div>
    @else
        <form id="shipment-form" method="POST" enctype="multipart/form-data" novalidate
              action="{{ $isEdit ? route('shipments.update', $shipment) : route('shipments.store') }}"
              @if ($isEdit)
                  data-nominal="{{ (float) $shipment->order->volume_kg }}"
                  data-tol="{{ (float) $shipment->order->toleransi_tonase_persen }}"
              @endif
              class="space-y-6">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            {{-- 1. Order --}}
            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold">1. Order</h2>

                @if ($isEdit)
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-zinc-500">Nomor PO</dt><dd class="font-medium">{{ $shipment->order->nomor_po }}</dd></div>
                        <div><dt class="text-zinc-500">Material</dt><dd class="font-medium">{{ $shipment->order->material_name }}</dd></div>
                        <div><dt class="text-zinc-500">Buyer</dt><dd class="font-medium">{{ $shipment->order->buyer?->nama }}</dd></div>
                        <div><dt class="text-zinc-500">Volume nominal</dt><dd class="font-medium">{{ number_format((float) $shipment->order->volume_kg, 2, ',', '.') }} kg</dd></div>
                    </dl>
                @else
                    <div class="mt-4">
                        <label for="po-select" class="label">Pilih order <span class="text-red-500">*</span></label>
                        <select id="po-select" name="purchase_order_id" required class="input @error('purchase_order_id') input-error @enderror">
                            <option value="">Pilih order...</option>
                            @foreach ($orders as $o)
                                <option value="{{ $o->id }}"
                                        data-nominal="{{ (float) $o->volume_kg }}"
                                        data-tol="{{ (float) $o->toleransi_tonase_persen }}"
                                        data-buyer="{{ $o->buyer?->nama }}"
                                        data-material="{{ $o->material_name }}"
                                        data-dest="{{ $o->buyer?->alamat }}"
                                        data-origin="{{ $o->seller?->alamat ?? auth()->user()->company?->alamat }}"
                                        @selected((int) old('purchase_order_id', $selectedOrderId) === $o->id)>
                                    {{ $o->nomor_po }} - {{ $o->material_name }} ({{ number_format((float) $o->volume_kg, 0, ',', '.') }} kg)
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div id="po-info" class="mt-4 hidden rounded-md border border-zinc-200 bg-zinc-50 p-4 text-sm">
                    <dl class="grid gap-3 sm:grid-cols-2">
                        <div><dt class="text-zinc-500">Buyer</dt><dd id="info-buyer" class="font-medium"></dd></div>
                        <div><dt class="text-zinc-500">Material</dt><dd id="info-material" class="font-medium"></dd></div>
                        <div><dt class="text-zinc-500">Volume nominal</dt><dd id="info-nominal" class="font-medium"></dd></div>
                        <div><dt class="text-zinc-500">Rentang berat yang diizinkan</dt><dd id="info-range" class="font-medium"></dd></div>
                    </dl>
                </div>
            </section>

            {{-- 2. Armada & rute --}}
            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold">2. Armada &amp; Rute</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @php
                        $fields = [
                            ['armada', 'Jenis armada', 'text', 'Contoh: Mitsubishi L300', true],
                            ['plat', 'Plat nomor', 'text', 'D 1234 ABC', true],
                            ['sopir', 'Nama sopir', 'text', '', true],
                            ['kontak_sopir', 'Kontak sopir', 'text', '0812xxxxxxx', false],
                        ];
                    @endphp
                    @foreach ($fields as [$name, $label, $type, $ph, $req])
                        <div>
                            <label for="{{ $name }}" class="label">{{ $label }} @if ($req)<span class="text-red-500">*</span>@endif</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" placeholder="{{ $ph }}"
                                   value="{{ old($name, $shipment?->{$name}) }}"
                                   class="input @error($name) input-error @enderror">
                            @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach

                    <div>
                        <label for="jadwal_berangkat" class="label">Jadwal berangkat <span class="text-red-500">*</span></label>
                        <input id="jadwal_berangkat" name="jadwal_berangkat" type="datetime-local"
                               value="{{ old('jadwal_berangkat', $fmtDt($shipment?->jadwal_berangkat)) }}"
                               class="input @error('jadwal_berangkat') input-error @enderror">
                        @error('jadwal_berangkat')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="estimasi_tiba" class="label">Estimasi tiba <span class="text-red-500">*</span></label>
                        <input id="estimasi_tiba" name="estimasi_tiba" type="datetime-local"
                               value="{{ old('estimasi_tiba', $fmtDt($shipment?->estimasi_tiba)) }}"
                               class="input @error('estimasi_tiba') input-error @enderror">
                        @error('estimasi_tiba')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="rute_asal" class="label">Rute asal <span class="text-red-500">*</span></label>
                        <input id="rute_asal" name="rute_asal" type="text" value="{{ old('rute_asal', $shipment?->rute_asal) }}"
                               class="input @error('rute_asal') input-error @enderror">
                        @error('rute_asal')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="rute_tujuan" class="label">Rute tujuan <span class="text-red-500">*</span></label>
                        <input id="rute_tujuan" name="rute_tujuan" type="text" value="{{ old('rute_tujuan', $shipment?->rute_tujuan) }}"
                               class="input @error('rute_tujuan') input-error @enderror">
                        @error('rute_tujuan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="jarak_km" class="label">Perkiraan jarak (km)</label>
                        <input id="jarak_km" name="jarak_km" type="number" step="0.01" min="0" value="{{ old('jarak_km', $shipment?->jarak_km) }}"
                               class="input @error('jarak_km') input-error @enderror">
                        @error('jarak_km')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- 3. Muatan --}}
            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold">3. Muatan &amp; Dokumen</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="berat_dikirim_kg" class="label">Berat dikirim (kg) <span class="text-red-500">*</span></label>
                        <input id="berat_dikirim_kg" name="berat_dikirim_kg" type="number" step="0.01" min="0"
                               value="{{ old('berat_dikirim_kg', $shipment?->berat_dikirim_kg) }}"
                               class="input @error('berat_dikirim_kg') input-error @enderror">
                        @error('berat_dikirim_kg')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p id="weight-hint" class="mt-1.5 hidden text-xs"></p>
                    </div>

                    <div id="reason-wrap" class="{{ old('alasan_di_bawah_batas', $shipment?->alasan_di_bawah_batas) ? '' : 'hidden' }} sm:col-span-2">
                        <label for="alasan_di_bawah_batas" class="label">Alasan berat di bawah batas toleransi <span class="text-red-500">*</span></label>
                        <textarea id="alasan_di_bawah_batas" name="alasan_di_bawah_batas" rows="2"
                                  class="input @error('alasan_di_bawah_batas') input-error @enderror">{{ old('alasan_di_bawah_batas', $shipment?->alasan_di_bawah_batas) }}</textarea>
                        @error('alasan_di_bawah_batas')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="catatan" class="label">Catatan</label>
                        <textarea id="catatan" name="catatan" rows="2" class="input">{{ old('catatan', $shipment?->catatan) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="dokumen" class="label">Surat jalan internal (opsional)</label>
                        <input id="dokumen" name="dokumen[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full text-sm text-zinc-600 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-zinc-200">
                        <p class="mt-1 text-xs text-zinc-500">PDF, JPG, atau PNG. Maksimal {{ config('symbionusa.shipment_max_documents') }} berkas, masing-masing 5 MB.</p>
                        @error('dokumen')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('dokumen.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ $isEdit ? route('shipments.show', $shipment) : route('shipments.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Draf</button>
            </div>
        </form>

        {{-- Surat jalan yang sudah ada (form terpisah, tidak bersarang) --}}
        @if ($isEdit && $shipment->documents->isNotEmpty())
            <section class="card mt-6 p-5 sm:p-6">
                <h2 class="text-base font-semibold">Surat jalan tersimpan</h2>
                <ul class="mt-3 divide-y divide-zinc-100 text-sm">
                    @foreach ($shipment->documents as $d)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <a href="{{ route('shipments.documents.download', [$shipment, $d]) }}" class="truncate font-medium text-emerald-700 hover:underline">{{ $d->nama_asli }}</a>
                            <span class="flex items-center gap-3">
                                <span class="text-xs text-zinc-500">{{ $d->ukuran_kb }} KB</span>
                                <form method="POST" action="{{ route('shipments.documents.destroy', [$shipment, $d]) }}" onsubmit="return confirm('Hapus surat jalan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-medium text-red-600 hover:underline">Hapus</button>
                                </form>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('shipment-form');
    if (!form) return;

    const select = document.getElementById('po-select');
    const info = document.getElementById('po-info');
    const weight = document.getElementById('berat_dikirim_kg');
    const hint = document.getElementById('weight-hint');
    const reasonWrap = document.getElementById('reason-wrap');
    const fmt = (n) => Number(n).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function source() {
        if (select) {
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) return null;
            return { nominal: +opt.dataset.nominal, tol: +opt.dataset.tol, buyer: opt.dataset.buyer, material: opt.dataset.material, opt };
        }
        return { nominal: +form.dataset.nominal, tol: +form.dataset.tol };
    }

    function refresh() {
        const s = source();
        if (!s) { info.classList.add('hidden'); hint.classList.add('hidden'); return; }

        const min = Math.round(s.nominal * (1 - s.tol / 100) * 100) / 100;
        const max = Math.round(s.nominal * (1 + s.tol / 100) * 100) / 100;

        if (s.buyer !== undefined) {
            document.getElementById('info-buyer').textContent = s.buyer || '-';
            document.getElementById('info-material').textContent = s.material || '-';
        }
        document.getElementById('info-nominal').textContent = fmt(s.nominal) + ' kg (toleransi ' + s.tol + '%)';
        document.getElementById('info-range').textContent = fmt(min) + ' - ' + fmt(max) + ' kg';
        info.classList.remove('hidden');

        const w = parseFloat(weight.value);
        hint.className = 'mt-1.5 text-xs';
        if (isNaN(w)) { hint.classList.add('hidden'); return; }

        const r = Math.round(w * 100) / 100;
        if (r > max) {
            hint.textContent = 'Melebihi batas atas (' + fmt(max) + ' kg). Pengiriman akan ditolak.';
            hint.classList.add('text-red-600');
            reasonWrap.classList.add('hidden');
        } else if (r < min) {
            hint.textContent = 'Di bawah batas bawah (' + fmt(min) + ' kg). Boleh, tetapi alasan wajib diisi.';
            hint.classList.add('text-amber-600');
            reasonWrap.classList.remove('hidden');
        } else {
            hint.textContent = 'Berat dalam rentang toleransi.';
            hint.classList.add('text-emerald-700');
            reasonWrap.classList.add('hidden');
        }
    }

    if (select) {
        select.addEventListener('change', () => {
            const s = source();
            if (s) {
                const o = s.opt.dataset;
                const asal = document.getElementById('rute_asal');
                const tujuan = document.getElementById('rute_tujuan');
                if (asal && !asal.value && o.origin) asal.value = o.origin;
                if (tujuan && !tujuan.value && o.dest) tujuan.value = o.dest;
            }
            refresh();
        });
    }
    weight.addEventListener('input', refresh);
    refresh();
})();
</script>
@endpush
