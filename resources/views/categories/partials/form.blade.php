{{--
    Isi form kategori, dipakai halaman Tambah dan Edit.
    $category: null untuk Tambah, model Category untuk Edit. $parentCategories: pilihan induk.
--}}
@php
    $value = fn (string $field, $default = null) => old($field, $category?->{$field} ?? $default);
    $defaultSpec = $category
        ? ($category->spec_schema ?? [])
        : [
            ['nama' => 'Kadar Air', 'tipe' => 'number', 'satuan' => '%', 'wajib' => true],
            ['nama' => 'Ukuran Partikel', 'tipe' => 'text', 'satuan' => 'cm', 'wajib' => false],
            ['nama' => 'Tingkat Kebersihan', 'tipe' => 'select', 'satuan' => '', 'wajib' => true],
        ];
    $checkbox = 'mt-0.5 size-4 shrink-0 rounded-sm accent-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring';
    $suffix = 'pointer-events-none absolute inset-y-0 right-3 flex items-center text-body-md text-on-surface-muted';
@endphp

<x-ui.card title="Informasi pokok & hierarki">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <x-ui.field label="Nama kategori" for="nama" name="nama" helper="Maksimal 100 karakter." required>
            <x-ui.input id="nama" name="nama" :value="$value('nama')" required maxlength="100"
                placeholder="Mis. Plastik PP Scrap Bersih" aria-describedby="nama-helper" />
        </x-ui.field>

        <x-ui.field label="Kategori induk" for="categories_id_categories" name="categories_id_categories"
            helper="Kosongkan untuk kategori tingkat teratas; pilih induk untuk membuat subkategori.">
            <x-ui.select id="categories_id_categories" name="categories_id_categories" aria-describedby="categories_id_categories-helper">
                <option value="">Tanpa induk (tingkat teratas)</option>
                @foreach ($parentCategories as $parent)
                    <option value="{{ $parent->id_categories }}" @selected((string) $value('categories_id_categories') === (string) $parent->id_categories)>
                        {{ $parent->nama }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        @if ($category)
            <x-ui.field label="Status" for="status" name="status"
                helper="Kategori yang diarsipkan tidak bisa dipilih untuk listing baru.">
                <x-ui.select id="status" name="status" aria-describedby="status-helper">
                    <option value="AKTIF" @selected($value('status') === 'AKTIF')>Aktif</option>
                    <option value="ARSIP" @selected($value('status') === 'ARSIP')>Diarsipkan</option>
                </x-ui.select>
            </x-ui.field>
        @endif
    </div>
</x-ui.card>

<x-ui.card title="Batas toleransi & mutu">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <x-ui.field label="Toleransi susut (%)" for="toleransi_susut_persen" name="toleransi_susut_persen"
            helper="Disalin ke PO saat dibuat. Rentang 0–100." required>
            <div class="relative">
                <x-ui.input id="toleransi_susut_persen" name="toleransi_susut_persen" type="number" class="pr-10 tabular-nums"
                    :value="$value('toleransi_susut_persen', '2.00')" required min="0" max="100" step="0.01"
                    aria-describedby="toleransi_susut_persen-helper" />
                <span class="{{ $suffix }}" aria-hidden="true">%</span>
            </div>
        </x-ui.field>

        <x-ui.field label="Kadar air maks. (%)" for="kadar_air_maks_persen" name="kadar_air_maks_persen"
            helper="Opsional. Batas kadar air yang diterima.">
            <div class="relative">
                <x-ui.input id="kadar_air_maks_persen" name="kadar_air_maks_persen" type="number" class="pr-10 tabular-nums"
                    :value="$value('kadar_air_maks_persen')" min="0" max="100" step="0.01" placeholder="Mis. 5,00"
                    aria-describedby="kadar_air_maks_persen-helper" />
                <span class="{{ $suffix }}" aria-hidden="true">%</span>
            </div>
        </x-ui.field>

        <x-ui.field label="Toleransi kadar air (poin)" for="toleransi_kadar_air_poin" name="toleransi_kadar_air_poin"
            helper="Opsional. Selisih yang masih diterima.">
            <div class="relative">
                <x-ui.input id="toleransi_kadar_air_poin" name="toleransi_kadar_air_poin" type="number" class="pr-12 tabular-nums"
                    :value="$value('toleransi_kadar_air_poin')" min="0" max="100" step="0.01" placeholder="Mis. 0,50"
                    aria-describedby="toleransi_kadar_air_poin-helper" />
                <span class="{{ $suffix }}" aria-hidden="true">poin</span>
            </div>
        </x-ui.field>
    </div>
</x-ui.card>

<x-ui.card title="Risiko & larangan B3">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <x-ui.field label="Tingkat risiko inspeksi" for="tingkat_risiko" name="tingkat_risiko"
            helper="Rendah: disarankan verifikasi dokumen. Tinggi: disarankan uji lapangan." required>
            <x-ui.select id="tingkat_risiko" name="tingkat_risiko" required aria-describedby="tingkat_risiko-helper">
                <option value="RENDAH" @selected($value('tingkat_risiko', 'RENDAH') === 'RENDAH')>Risiko Rendah</option>
                <option value="TINGGI" @selected($value('tingkat_risiko', 'RENDAH') === 'TINGGI')>Risiko Tinggi</option>
            </x-ui.select>
        </x-ui.field>

        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <input type="checkbox" id="wajib_sertifikat_lab" name="wajib_sertifikat_lab" value="1"
                    @checked($value('wajib_sertifikat_lab')) class="{{ $checkbox }}" aria-describedby="wajib_sertifikat_lab-desc">
                <div>
                    <label for="wajib_sertifikat_lab" class="text-label-md text-on-surface">Wajib sertifikat uji laboratorium</label>
                    <p id="wajib_sertifikat_lab-desc" class="text-body-sm text-on-surface-muted">Seller harus melampirkan hasil uji lab sebelum listing diverifikasi.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-md bg-error-container p-4 text-on-error-container">
                <input type="checkbox" id="is_b3_prohibited" name="is_b3_prohibited" value="1"
                    @checked($value('is_b3_prohibited')) class="{{ $checkbox }}" aria-describedby="is_b3_prohibited-desc">
                <div>
                    <label for="is_b3_prohibited" class="text-label-md">Tandai sebagai B3 (dilarang)</label>
                    <p id="is_b3_prohibited-desc" class="text-body-sm">Listing baru dengan kategori ini otomatis <strong>ditolak</strong> sistem.</p>
                </div>
            </div>
        </div>
    </div>
</x-ui.card>

<x-ui.card title="Templat spesifikasi">
    <x-slot:actions>
        <x-ui.button variant="secondary" id="btn-add-param">
            <x-ui.icon name="plus" />
            Tambah Parameter
        </x-ui.button>
    </x-slot:actions>

    <p class="-mt-2 mb-4 text-body-sm text-on-surface-muted">
        Parameter mutu yang diisi Seller saat membuat listing dengan kategori ini.
    </p>

    <div id="params-container" class="space-y-3"></div>
    <p id="params-empty" class="hidden rounded-md border border-dashed border-outline-input px-4 py-6 text-center text-body-sm text-on-surface-muted">
        Belum ada parameter. Pilih "Tambah Parameter" untuk menambahkan.
    </p>

    {{-- Satu baris parameter; disalin oleh skrip di bawah. "__N__" diganti nomor baris. --}}
    <template id="param-row-template">
        <div class="param-row grid grid-cols-1 items-end gap-3 rounded-md border border-outline bg-background p-3 sm:grid-cols-12">
            <x-ui.field label="Nama parameter" for="param-nama-__N__" class="sm:col-span-4">
                <x-ui.input id="param-nama-__N__" class="param-nama" placeholder="Mis. Kebersihan" />
            </x-ui.field>
            <x-ui.field label="Tipe" for="param-tipe-__N__" class="sm:col-span-3">
                <x-ui.select id="param-tipe-__N__" class="param-tipe">
                    <option value="text">Teks</option>
                    <option value="number">Angka</option>
                    <option value="select">Pilihan</option>
                    <option value="boolean">Ya/Tidak</option>
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="Satuan" for="param-satuan-__N__" class="sm:col-span-2">
                <x-ui.input id="param-satuan-__N__" class="param-satuan" placeholder="Mis. %" />
            </x-ui.field>
            <div class="flex h-10 items-center sm:col-span-2">
                <label class="flex cursor-pointer items-center gap-2 text-label-md text-on-surface">
                    <input type="checkbox" class="param-wajib size-4 shrink-0 rounded-sm accent-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
                    Wajib
                </label>
            </div>
            <div class="flex h-10 items-center sm:col-span-1 sm:justify-end">
                <x-ui.button variant="text-danger" class="btn-remove-param">Hapus</x-ui.button>
            </div>
        </div>
    </template>

    <details class="mt-6 border-t border-outline pt-4">
        <summary class="cursor-pointer text-label-md text-on-surface hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring">
            Mode lanjutan: edit JSON templat
        </summary>
        <div class="mt-3">
            <label for="spec_schema_json" class="sr-only">JSON templat spesifikasi</label>
            <x-ui.textarea id="spec_schema_json" name="spec_schema_json" rows="6" class="font-mono text-body-sm"
                placeholder='[{"nama": "Kadar Air", "tipe": "number", "satuan": "%", "wajib": true}]'>{{ old('spec_schema_json', json_encode(old('spec_schema', $defaultSpec), JSON_PRETTY_PRINT)) }}</x-ui.textarea>
        </div>
    </details>
</x-ui.card>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('params-container');
    const empty = document.getElementById('params-empty');
    const jsonTextarea = document.getElementById('spec_schema_json');
    const btnAdd = document.getElementById('btn-add-param');
    const template = document.getElementById('param-row-template');
    let rowCount = 0;

    let initialParams = [];
    try {
        initialParams = JSON.parse(jsonTextarea.value || '[]');
        if (!Array.isArray(initialParams)) initialParams = [];
    } catch (e) {
        initialParams = [];
    }

    function updateEmpty() {
        empty.classList.toggle('hidden', container.children.length > 0);
    }

    function syncToJson() {
        const params = [];
        container.querySelectorAll('.param-row').forEach(row => {
            const nama = row.querySelector('.param-nama').value.trim();
            if (nama !== '') {
                params.push({
                    nama,
                    tipe: row.querySelector('.param-tipe').value,
                    satuan: row.querySelector('.param-satuan').value.trim(),
                    wajib: row.querySelector('.param-wajib').checked,
                });
            }
        });
        jsonTextarea.value = JSON.stringify(params, null, 2);
    }

    function renderRow(param = { nama: '', tipe: 'text', satuan: '', wajib: false }) {
        const n = ++rowCount;
        const html = template.innerHTML.replaceAll('__N__', n);
        const holder = document.createElement('div');
        holder.innerHTML = html.trim();
        const div = holder.firstElementChild;

        div.querySelector('.param-nama').value = param.nama || '';
        div.querySelector('.param-tipe').value = ['text', 'number', 'select', 'boolean'].includes(param.tipe) ? param.tipe : 'text';
        div.querySelector('.param-satuan').value = param.satuan || '';
        div.querySelector('.param-wajib').checked = !! param.wajib;

        div.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('input', syncToJson);
            el.addEventListener('change', syncToJson);
        });

        div.querySelector('.btn-remove-param').addEventListener('click', function () {
            div.remove();
            syncToJson();
            updateEmpty();
            btnAdd.focus();
        });

        container.appendChild(div);
        updateEmpty();
        return div;
    }

    initialParams.forEach(p => renderRow(p));
    updateEmpty();

    btnAdd.addEventListener('click', function () {
        renderRow().querySelector('.param-nama').focus();
    });

    document.getElementById('category-form').addEventListener('submit', syncToJson);
});
</script>
