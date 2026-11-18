{{--
    Isi form Inspector (SCREENS 03), dipakai halaman Daftarkan dan Ubah.
    $inspector: null untuk Daftarkan, InspectorProfile untuk Ubah.
    $candidates: akun INSPECTOR yang belum terdaftar (hanya Daftarkan).
    $expertiseOptions: kategori aktif non-B3; $provinces: daftar provinsi.
--}}
@use('App\Support\Format')
@use('App\Http\Requests\Inspector\InspectorRules')
@use('App\Models\InspectorProfile')

@php
    $selectedExpertise = array_map('intval', (array) old('bidang_keahlian', $inspector?->expertiseIds() ?? []));
    $selectedWilayah = old('wilayah_tugas', $inspector?->wilayah_tugas);
    $maxMb = (int) (InspectorRules::maxUploadKb() / 1024);
    $chip = 'flex min-h-10 cursor-pointer items-center gap-2 rounded-sm border border-outline-input bg-surface px-3 py-2 text-body-md text-on-surface transition-colors hover:bg-background has-[:checked]:border-primary has-[:checked]:bg-primary-container has-[:checked]:text-on-primary-container has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-focus-ring';
    $expertiseError = $errors->first('bidang_keahlian') ?: $errors->first('bidang_keahlian.*');
@endphp

<x-ui.card title="Akun pengguna">
    @if ($inspector)
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-label-md text-on-surface-muted">Nama</dt>
                <dd class="mt-1 text-body-md text-on-surface">{{ $inspector->user->nama }}</dd>
            </div>
            <div>
                <dt class="text-label-md text-on-surface-muted">Email</dt>
                <dd class="mt-1 text-body-md text-on-surface">{{ $inspector->user->email }}</dd>
            </div>
        </dl>
    @elseif ($candidates->isEmpty())
        <x-ui.alert variant="warning" title="Belum ada akun Inspector yang bisa didaftarkan">
            Semua akun berperan Inspector sudah terdaftar, atau belum ada akun Inspector aktif. Buat akun pengguna
            berperan Inspector terlebih dahulu.
        </x-ui.alert>
    @else
        <x-ui.field label="Akun pengguna (peran Inspector)" for="id_user" name="id_user"
            helper="Hanya akun Inspector aktif yang belum ada di registri." required>
            <x-ui.select id="id_user" name="id_user" required aria-describedby="id_user-helper">
                <option value="">Pilih akun…</option>
                @foreach ($candidates as $candidate)
                    <option value="{{ $candidate->id_user }}" @selected((string) old('id_user') === (string) $candidate->id_user)>
                        {{ $candidate->nama }} · {{ $candidate->email }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>
    @endif
</x-ui.card>

<x-ui.card title="Kompetensi">
    <div class="space-y-6">
        <fieldset aria-describedby="bidang_keahlian-helper{{ $expertiseError ? ' bidang_keahlian-error' : '' }}">
            <legend class="text-label-md text-on-surface">
                Bidang keahlian (kategori material)
                <span class="text-error" aria-hidden="true">*</span>
                <span class="sr-only">(wajib)</span>
            </legend>
            <p id="bidang_keahlian-helper" class="mt-1 text-body-sm text-on-surface-muted">
                Pilih satu atau lebih. Penugasan inspeksi hanya menampilkan Inspector yang keahliannya sesuai kategori order.
            </p>

            @if ($expertiseOptions->isEmpty())
                <p class="mt-3 rounded-md border border-dashed border-outline-input px-4 py-6 text-center text-body-sm text-on-surface-muted">
                    Belum ada kategori material aktif. Tambahkan kategori di menu Kategori Material terlebih dahulu.
                </p>
            @else
                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($expertiseOptions as $cat)
                        @php $isLegacy = $cat->status !== 'AKTIF' || $cat->is_b3_prohibited; @endphp
                        <label class="{{ $chip }}">
                            <input type="checkbox" name="bidang_keahlian[]" value="{{ $cat->id_categories }}"
                                @checked(in_array($cat->id_categories, $selectedExpertise, true))
                                class="size-4 shrink-0 rounded-sm accent-primary focus-visible:outline-none">
                            <span class="min-w-0">
                                <span class="block truncate">{{ $cat->nama }}</span>
                                @if ($cat->parent)
                                    <span class="block truncate text-body-sm text-on-surface-muted">{{ $cat->parent->nama }}</span>
                                @endif
                                @if ($isLegacy)
                                    <span class="block text-body-sm text-error">Tidak aktif lagi; hapus centang</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif

            @if ($expertiseError)
                <p id="bidang_keahlian-error" class="mt-2 text-body-sm text-error">{{ $expertiseError }}</p>
            @endif
        </fieldset>

        <x-ui.field label="Wilayah tugas" for="wilayah_tugas" name="wilayah_tugas" class="max-w-md"
            helper="Satu provinsi per Inspector; dipakai menyaring penugasan mode Lapangan." required>
            <x-ui.select id="wilayah_tugas" name="wilayah_tugas" required aria-describedby="wilayah_tugas-helper">
                <option value="">Pilih provinsi…</option>
                @foreach ($provinces as $prov)
                    <option value="{{ $prov }}" @selected($selectedWilayah === $prov)>{{ $prov }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.field>
    </div>
</x-ui.card>

<x-ui.card title="Sertifikasi kompetensi">
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-ui.field label="Nomor sertifikat" for="no_sertifikat" name="no_sertifikat" helper="Maksimal 100 karakter." required>
                <x-ui.input id="no_sertifikat" name="no_sertifikat" :value="old('no_sertifikat', $inspector?->no_sertifikat)"
                    required maxlength="100" placeholder="Mis. INS-TEK-2025-017" class="tabular-nums" aria-describedby="no_sertifikat-helper" />
            </x-ui.field>

            @if ($inspector)
                <div class="space-y-1.5">
                    <p class="text-label-md text-on-surface">Berlaku sampai</p>
                    <p class="flex h-10 items-center text-body-md tabular-nums text-on-surface">{{ Format::date($inspector->berlaku_sampai) }}</p>
                    <p class="text-body-sm text-on-surface-muted">
                        Ubah masa berlaku lewat
                        <a href="{{ route('admin.inspectors.renew.form', $inspector) }}" class="text-primary underline underline-offset-2 hover:text-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">Perpanjang sertifikat</a>.
                    </p>
                </div>
            @else
                <x-ui.field label="Berlaku sampai" for="berlaku_sampai" name="berlaku_sampai"
                    helper="Harus setelah hari ini. Inspector otomatis nonaktif setelah tanggal ini." required>
                    <x-ui.input id="berlaku_sampai" name="berlaku_sampai" type="date" class="tabular-nums"
                        :value="old('berlaku_sampai')" required min="{{ InspectorProfile::today()->addDay()->toDateString() }}" aria-describedby="berlaku_sampai-helper" />
                </x-ui.field>
            @endif
        </div>

        @include('inspectors.partials.file-field', [
            'label' => $inspector ? 'Ganti berkas sertifikat' : 'Berkas sertifikat',
            'required' => ! $inspector,
            'helper' => $inspector
                ? 'Opsional. Kosongkan bila berkas tidak berubah. PDF, JPG, atau PNG, maks. '.$maxMb.' MB.'
                : 'PDF, JPG, atau PNG, maks. '.$maxMb.' MB.',
        ])
    </div>
</x-ui.card>
