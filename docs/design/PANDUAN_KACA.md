# Panduan Kaca — membangun layar SymbioNusa dengan gaya Kaca

> **Untuk siapa.** Agent dan anggota tim (Claude Code, Antigravity, Codex, OpenCode) yang membangun atau membangun ulang layar SymbioNusa. Panduan ini praktis: nama kelas, fungsi, dan cuplikan yang bisa langsung ditiru. Aturan normatifnya ada di `docs/design/DESIGN.md` (FND v12); kalau panduan ini dan DESIGN.md berbeda, DESIGN.md yang menang, dan selisihnya dilaporkan sebagai temuan.
>
> **Keputusan PM (Mayhesa, 25 Nov 2026).** Kaca adalah standar visual untuk **seluruh aplikasi**: semua dasbor, form, tabel, halaman detail, dan halaman autentikasi. Sumber visualnya varian *Kaca* dari eksperimen SymbioNusa Lab (`D:\PPL\SymbioNusa-Lab`, `src/index.css` blok `[data-variant="kaca"]`, `src/App.jsx`, `src/components`, `src/charts`).
>
> **Sumber kebenaran kode:** dasbor Admin di branch `mayhesa` (commit `b8a3e24`, ditambah perbaikan 25 Nov 2026: pembungkus bersama `x-app.kaca`, varian Kaca `x-ui.table`/`x-ui.summary-strip`, penanda `.kaca-flag`, tombol jeda gerak). Semua nama di panduan ini dicek terhadap kode itu.

## 1. Status implementasi (jujur)

| Layar | Status per 25 Nov 2026 |
|---|---|
| Dasbor Admin (`/dashboard`, peran ADMIN) | **Sudah Kaca**, lewat `<x-app.kaca>`. Satu-satunya layar yang sudah memakai Kaca. |
| Sidebar | Menjadi kaca otomatis **hanya saat** halaman memuat `.dash-kaca` (`body:has(.dash-kaca) [data-sidebar]`). Di halaman lain masih sidebar Senja solid. |
| Dasbor Seller, Buyer, Inspector | Masih Senja di Sawah (`.dash-senja`, kartu putih). Perlu dibangun ulang mengikuti panduan ini. |
| Form, tabel, halaman detail (kategori, registri Inspector, verifikasi mitra, dll.) | Masih Senja (kartu putih, `x-ui.*`). Perlu dibangun ulang. |
| Login, lupa sandi, reset sandi | Masih Senja + panel foto. Perlu dibangun ulang: panel foto tetap, sisi form di atas latar Kaca (§12). |
| Landing | Permukaan publik; tidak diubah ke Kaca (keputusan §12). |

`tests/Feature/DashboardTest.php` saat ini **menegaskan** bahwa dasbor Seller tidak memuat `dash-kaca` (`assertDontSee('dash-kaca')`). Saat dasbor Seller dibangun ulang dengan Kaca, ubah asersi itu di PR yang sama (pemilik test: lihat `AGENTS.md` §4).

## 2. Cara kerja dalam satu gambar

```
<html> kanvas krem #fdf8d8
 └ .kaca-backdrop (fixed, z -1)  empat foto material diburamkan (.kaca-blob, blur 38px, saturate 165%, melayang pelan)
     ::before  kerudung krem 180° (alpha .52 → .22 di 45% → .42)
     ::after   butir halus (SVG fractal noise, opacity .16, multiply)
 └ .dash-kaca (pembungkus isi)  mendefinisikan token Kaca + kurva spring
     ├ kartu kaca  [data-card] atau .glass   krem .66 → .36 → .50, blur 22px saturate 150%, tepi putih 28%, radius 22px
     │   ├ baris .glass-row + bg-field   putih .55 di atas kaca: permukaan baris dan sub-panel
     │   └ kontrol (tombol, input, select)  tetap putih pekat / hijau terisi, TIDAK transparan
     ├ kaca tebal .glass-strong          laci, palet, dialog, tooltip (krem .94 → .86, blur 40px)
     └ .impact-band                      band hutan gelap (satu-satunya bidang gelap di isi)
```

Kaca hanya mengganti **material permukaan**. Palet Senja, tipografi Figtree, Ledger (tanpa pill di isi, status = teks + glyph), Text budget, Data honesty, dan Interaction integrity tetap berlaku.

## 3. Bahan yang sudah ada di repo

| Bahan | Berkas | Keterangan |
|---|---|---|
| CSS Kaca | `resources/css/dashboard-kaca.css` (diimpor di `resources/css/app.css`) | Token, latar, kartu, kelas komponen, gerak, cadangan. |
| Alpine Kaca | `resources/js/dashboard/kaca.js` | `springCurve()`, `flip()`, `exit()`, `kacaPage()`, `kacaRiver()`, `kacaSegment()`, `kacaAdmin()`. |
| Komponen bersama | `x-ui.card`, `x-ui.summary-strip`, `x-ui.table` | Otomatis Kaca di dalam `.dash-kaca` lewat kait `data-card`, `data-summary`, `data-table`. |
| Registrasi | `resources/js/app.js` | `Alpine.data('kacaPage')`, `Alpine.data('kacaAdmin')`, `Alpine.data('kacaRiver')`, `Alpine.directive('kaca-seg')`. `flip`/`exit` **diekspor tetapi tidak didaftarkan global**; impor dari modul Anda. |
| Pembungkus + latar | `resources/views/components/app/kaca.blade.php` (`<x-app.kaca>`) | `.dash-kaca`, `.kaca-backdrop` (empat foto), kilau kursor, tombol jeda gerak latar. Alpine `kacaPage`. Prop `enabled` untuk layar yang Kaca-nya bergantung pada peran. |
| Contoh layar lengkap | `resources/views/livewire/dashboard/admin.blade.php` + `resources/views/livewire/dashboard/kaca/*.blade.php` | header, summary, queue, hourly, river, river-stage, region, aging, panels, overlays. |
| Penyedia data | `app/Services/Dashboard/AdminKaca.php` (dipanggil dari `DashboardService::admin()` sebagai `$data['kaca']`) | Pola: satu kelas `build()` yang meratakan data DB ke bentuk yang dipakai view, dengan tanda demo yang eksplisit. |
| Angka matriks titik | `app/Support/DotMatrix.php` | `DotMatrix::layout($teks, $dot = 6, $gap = 2.2)` → `width`, `height`, `dots[]` untuk band dampak. |
| Warna kategori | `resources/css/app.css` blok `@theme static` | `--color-category-plastik/-tekstil/-kayu/-kertas/-pangan/-lainnya`, dipakai lewat `var()` di SVG. |
| Foto | `public/images/material/*.jpg` | Kredit di `docs/design/CREDITS-FOTO.md`. |

### Kelas CSS (semua hanya aktif di dalam `.dash-kaca`)

| Kelas / kait | Fungsi |
|---|---|
| `.dash-kaca` | Pembungkus isi halaman (dirender oleh `<x-app.kaca>`; jangan ditulis tangan). Menyalakan token Kaca, kartu kaca, gerak spring, sidebar kaca. |
| `.kaca-still` / `.kaca-still-toggle` | Gerak latar dijeda / tombol jedanya (dari `<x-app.kaca>`). |
| `.kaca-flag` | Alas `bg-field` kecil (radius 6 px) untuk teks merah di kaca polos. |
| `[data-summary]`, `[data-table]` | Kait varian Kaca `x-ui.summary-strip` (sel bening, garis per sel) dan `x-ui.table` (garis hijau, kolom sticky buram hanya < 1024 px). |
| `.kaca-backdrop` > `.kaca-blob` ×4 | Latar foto buram. `aria-hidden="true"`, tanpa data. |
| `[data-card]` (otomatis dari `x-ui.card`) atau `.glass` | Kartu kaca. |
| `.glass-row` | Tepi cahaya + blur ringan untuk baris yang berdiri sendiri (baris antrean). Pasangkan dengan `bg-field border border-line rounded-[16px]`. |
| `.glass-strong` | Kaca tebal: laci, palet, dialog, tooltip grafik. |
| `.impact-band` | Band dampak hutan. |
| `.card-title` | Judul kartu 18 px / 800. |
| `.title-xl` | Judul halaman Kaca clamp(26–36 px) / 900. |
| `.mono` | Angka/ID monospace (ID order, pintasan keyboard). |
| `.hatch-bg` | Arsiran untuk nilai "terjadwal/rencana". |
| `.dash-grid` | Grid isi; anak diberi `min-width: 0`. |
| `.kaca-seg` + `.kaca-thumb` + `x-kaca-seg` | Kontrol segmen dengan thumb hijau meluncur. `.kaca-count` untuk hitungan di dalam tombol segmen. |
| `.kaca-spring` | Kelas transisi spring untuk `x-transition:enter` dan untuk lipatan `grid-rows`. |
| `[data-rise]` + `style="--i: n"` | Kartu naik + fade saat halaman dimuat, berjenjang 60 ms. |
| `.kaca-tile` / `.kaca-dot` + `--d` | Ubin/titik muncul (pop) dengan jeda `--d`. |
| `.kaca-arc` + `--c`, `--d` | Busur donat tergambar. |
| `.kaca-bar` + `--i` | Batang riwayat tumbuh saat baris dibuka (`button[aria-expanded="true"] + div`). |
| `.kaca-ping` | Denyut titik "langsung/berjalan". |
| `.shimmer` | Kilau pemuatan saat periode berganti. |
| `.river-flow` | Grup garis arus di Sungai Order (animasi `kaca-flow`, mati saat gerak tereduksi). |
| Keyframes | `kaca-grow-y` (batang SVG tumbuh), `kaca-flow`, `kaca-rise`, `kaca-pop`, `kaca-draw`. |

## 4. Token Kaca

Semua nilai di bawah sudah ada di kode; jangan menambah hex baru. Di Blade pakai kelas token (`text-meta`, `border-line`, `bg-field`, `bg-primary-soft`, `text-accent-text`, `bg-track`, `text-danger`, `bg-accent`, `text-accent-ink`, `text-primary-ink`, `text-ink`); di SVG dan `style` pakai `var(--…)`.

| Token | Nilai di dalam `.dash-kaca` | Nilai di luar `.dash-kaca` | Pakai untuk |
|---|---|---|---|
| `--color-ink` / `--ink` | #1c2a0a | #1c2a0a | Teks utama, teks di luar kartu (kepala halaman). |
| `--color-meta` / `--meta` | **#33330f** | #4a4a1a | Teks sekunder di kaca. |
| `--color-accent-text` | **#7c2d12** | #9a3412 | Teks jingga (penanda tahap tertahan, "lewat n hari"). |
| `--color-danger` (kelas `text-danger`; `.text-error` juga dipetakan ke sini) | **#7f1d1d** | #b91c1c | Teks merah; di kaca polos selalu di dalam `.kaca-flag`. |
| `--color-on-surface-muted` / `-subtle` | **#33330f** | #4a4a1a | Teks redup komponen `x-ui.*` (subjudul, label metrik, helper, kepala tabel) otomatis memakai meta Kaca. |
| `.text-primary`, `.text-success` | dipetakan ke **#2f3d12** (`--color-primary-hover`) | #3f6212 | Teks hijau (delta baik, status). |
| `--primary` | #3f6212 | — | Isi tombol, thumb segmen, seri utama grafik. |
| `--accent` | #c2410c | — | Isi kecil: lencana hitungan, penanda "Sekarang", ring fokus, tombol "Lihat n order". **Bukan warna teks.** |
| `--danger` | #b91c1c | — | **Isi** (ubin peringatan, batang). **Bukan warna teks** — untuk teks pakai `text-danger` / `var(--color-danger)`. |
| `--warning` / `--info` | #92400e / #1e40af | — | Teks status di baris `bg-field` atau kaca tebal saja. |
| `--line` / `--line-strong` | rgb(63 98 18 / .14) / .28 | — | Garis rambut / batas tombol bergaris di kaca. |
| `--field` (`bg-field`) | rgb(255 255 255 / .55) | — | Permukaan baris dan sub-panel di atas kaca. |
| `--primary-soft` | rgb(63 98 18 / .10) | — | Hover baris/tombol bergaris, baris terpilih. |
| `--accent-soft` | rgb(194 65 12 / .12) | — | Bidang insight satu baris (Sungai Order), bayangan fokus baris. |
| `--track` | rgb(63 98 18 / .10) | — | Trek bilah (umur, progres). |
| `--panel-strong` | rgb(255 253 242 / .84) | — | Baris/tahap terpilih, garis pemisah batang SVG. |
| `--scrim` | rgb(28 42 10 / .28) | — | Selubung di belakang laci/dialog. |
| `--hatch` | rgb(63 98 18 / .35) | — | Garis arsiran. |
| `--c-done / --c-run / --c-wait / --c-plan / --c-fail` | #3f6212 / #65a30d / #d97706 / #ddd08a / #b91c1c | — | Seri grafik status (selesai, berjalan, menunggu, terjadwal, gagal). |
| `--kaca-radius` | 22px | — | Radius kartu kaca. |
| `--kaca-blur` | 22px | — | Blur kartu. |
| `--kaca-a-hi / -mid / -lo` | .66 / .36 / .50 | — | Alpha gradien kartu (160°). Jangan diturunkan. |
| `--kaca-spring`, `--kaca-spring-ms` | `linear(…)` dari `springCurve()`; ±600 ms | — | Kurva dan durasi gerak spring (ditulis ke `<html>` oleh `kaca.js`). |

**Radius yang dipakai di Kaca:** kartu 22 px; tombol/segmen kaca di bilah atas 14 px (`rounded-[14px]!` karena `.glass` memaksa 22 px); baris antrean 16 px; tombol dan kontrol di dalam kartu 11–12 px; tombol di segmen 9–11 px (`--r` pada `.kaca-seg` mengikuti radius tombolnya); ID/tanda kecil 6 px; laci 24–26 px; band dampak 24 px.

## 5. Resep: membungkus sebuah layar

Bungkus seluruh isi view dengan komponen bersama `<x-app.kaca>` (`resources/views/components/app/kaca.blade.php`):

```blade
<x-app.kaca>
    <x-ui.page-header title="Kategori Material" subtitle="Kelola kategori yang dipakai di listing." />

    <div class="dash-grid grid gap-4 lg:grid-cols-12">
        <div class="lg:col-span-12" data-rise style="--i: 1">{{-- kartu kaca --}}</div>
    </div>
</x-app.kaca>
```

Komponen itu merender `.dash-kaca`, `.kaca-backdrop` dengan empat foto (`kain`, `ampas-tahu`, `serbuk-kayu`, `plastik-2`, ukuran dan posisi tetap), kilau kursor pada `[data-card]` (Alpine `kacaPage`), dan tombol jeda gerak latar di kanan bawah. Bila Kaca bergantung pada peran (seperti `/dashboard`), pakai `enabled`:

```blade
<x-app.kaca :enabled="$role === 'ADMIN'" :class="in_array($role, ['ADMIN', 'SELLER', 'BUYER', 'INSPECTOR'], true) ? 'dash-senja' : ''">
```

Di komponen Livewire, `<x-app.kaca>` boleh menjadi elemen akar: komponen ini selalu merender **satu** `div` akar (lihat §11, jebakan 13).

Catatan:
- **Jangan mengganti foto atau posisi blob.** Kontras di §9 diukur dengan empat foto dan posisi ini. Foto lain = ukur ulang.
- Pembungkus sudah memakai `x-data="kacaPage"`. Komponen Alpine layar ditaruh di elemen anak (seperti `admin.blade.php`), tidak di pembungkus.
- Kaca dipasang per layar, **tidak** lewat `layouts/app.blade.php`: layar Senja yang belum dibangun ulang akan rusak bila tiba-tiba berada di atas foto (kartu putih, teks meta Senja).
- Tombol jeda menambah ruang 64 px di bawah isi (`pb-16`) supaya tidak menutupi kartu terakhir.
- Kepala halaman: `x-ui.page-header` boleh dipakai. Di dalam `.dash-kaca`, subjudulnya (`h1 + p`) otomatis memakai tinta, bukan meta, karena teks di luar kartu duduk langsung di atas foto. Dasbor Admin memakai kepala sendiri (`kaca/header.blade.php`: `.title-xl`, bilah atas dengan palet, segmen periode).

## 6. Cuplikan siap tiru

Label setiap cuplikan: **[ada]** = salinan atau ringkasan kode yang sudah berjalan di `b8a3e24`; **[pola]** = pola baru yang memakai bahan yang sama tetapi belum ada di repo — uji sebelum PR.

### 6.1 Kartu kaca [ada]

```blade
{{-- Cara 1: komponen bersama. data-card membuatnya kaca di dalam .dash-kaca, putih di luar. --}}
<x-ui.card title="Daftar kategori">…</x-ui.card>

{{-- Cara 2: section manual (dipakai di semua partial kaca/*). --}}
<section class="glass flex h-full flex-col p-4 sm:p-5" aria-labelledby="aging-title">
    <h2 id="aging-title" class="card-title">Umur tagihan</h2>
    <p class="mt-1 text-[14px] text-meta">{{ $a['count'] }} invoice belum dibayar, dikelompokkan dari tanggal jatuh tempo.</p>
    …
</section>
```

Kilau kursor hanya muncul pada `[data-card]` (`kacaPage().sheen()` mencari `[data-card]`). `section.glass` tetap kaca, tanpa kilau yang mengikuti kursor.

### 6.2 Kontrol segmen dengan thumb [ada]

```blade
<div x-data="{ view: 'bar' }">
    <div class="kaca-seg flex shrink-0 rounded-[11px] border border-line-strong p-0.5" style="--r: 9px" x-kaca-seg role="group" aria-label="Bentuk grafik">
        <span class="kaca-thumb" aria-hidden="true"></span>
        @foreach (['bar' => 'Batang', 'dot' => 'Titik'] as $k => $l)
            <button type="button" @click="view = '{{ $k }}'" :aria-pressed="(view === '{{ $k }}').toString()"
                    class="h-9 rounded-[9px] px-2.5 text-[13px] font-bold">{{ $l }}</button>
        @endforeach
    </div>
</div>
```

Aturan: `.kaca-thumb` anak pertama; tombol memakai `aria-pressed` yang diikat Alpine (string `"true"`/`"false"`), karena `x-kaca-seg` mengamati atribut itu; `--r` sama dengan radius tombol. Tombol aktif otomatis bertulisan putih (`--primary-ink`); hitungan di dalam tombol pakai `.kaca-count`. Thumb tidak meluncur saat gerak tereduksi (langsung pindah).

### 6.3 Baris antrean dengan saringan FLIP

Markup baris [ada, dari `kaca/queue.blade.php`, diringkas]:

```blade
<ul class="relative flex-1 space-y-2" x-ref="queueList" aria-label="Antrean keputusan, urut dari risiko tertinggi">
    @foreach ($items as $i => $item)
        <li x-show="matches('{{ $item['kind'] }}')" data-kind="{{ $item['kind'] }}">
            <div tabindex="0" @keydown.enter.self.prevent="review({{ $i }})"
                 class="glass-row relative grid grid-cols-[44px_minmax(0,1fr)] gap-x-3 gap-y-2.5 rounded-[16px] border border-line bg-field p-3 outline-none hover:border-line-strong sm:grid-cols-[48px_minmax(0,1fr)_auto]">
                <img src="{{ $item['photo'] }}" alt="" class="size-11 rounded-[12px] object-cover sm:row-span-2 sm:size-12">
                <div class="min-w-0">
                    <span class="mono rounded-[6px] border border-line-strong px-1.5 py-px text-[12px] font-semibold text-meta">{{ $item['id'] }}</span>
                    <p class="text-[15px] leading-snug font-bold sm:truncate">{{ $item['title'] }}</p>
                    <p class="truncate text-[13px] text-meta">{{ $item['meta'] }}</p>
                </div>
                <span class="flex w-full items-center gap-1.5 sm:w-auto">
                    <button type="button" @click="review({{ $i }})" class="h-11 flex-1 rounded-[11px] border border-line-strong px-3 text-[13px] font-bold text-ink hover:bg-primary-soft sm:h-9 sm:flex-none">Tinjau</button>
                </span>
            </div>
        </li>
    @endforeach
</ul>
```

Logika saringan di layar baru [pola]. `kacaAdmin` khusus dasbor Admin (butuh `queue`, `periods`, `orders`, `commands`) — jangan dipakai ulang di layar lain. Buat komponen kecil yang mengimpor `flip()` dan `exit()`:

```js
// resources/js/<modul>/kaca-daftar.js  (pola; belum ada di repo)
import { exit, flip } from '../dashboard/kaca';

export function kacaDaftar(initial = 'semua') {
    return {
        filter: initial,
        busy: false,
        matches(kind) { return this.filter === 'semua' || this.filter === kind; },
        async setFilter(key) {
            if (key === this.filter || this.busy) return;
            this.busy = true;
            const list = this.$refs.list;
            const rows = () => [...list.querySelectorAll(':scope > li[data-kind]')];
            const leaving = rows().filter((li) => li.offsetParent !== null && !(key === 'semua' || li.dataset.kind === key));
            await exit(leaving);                                   // keluar cepat: geser + skala .96, 180 ms
            await flip(list, rows, () => { this.filter = key; }, () => this.$nextTick()); // sisa meluncur, tinggi wadah ikut
            leaving.forEach((li) => li.getAnimations().forEach((a) => a.cancel()));
            this.busy = false;
        },
    };
}
// resources/js/app.js, di dalam alpine:init:
// window.Alpine.data('kacaDaftar', kacaDaftar);
```

Kontrak `flip(container, items, mutate, nextTick)`: `items` adalah **fungsi** yang mengembalikan elemen; `mutate` mengubah state; `nextTick` menunggu DOM. Elemen yang sudah ada meluncur dengan spring; elemen baru masuk dengan fade + geser 16 px; tinggi wadah beranimasi. Saat gerak tereduksi hanya fade.

Saringan yang dijalankan **server** (Livewire `wire:click`, paginasi) tidak memakai FLIP: Livewire me-morph DOM dan memutus animasi. Pakai `wire:key` di setiap baris dan biarkan baris baru muncul dengan fade; jangan mencampur saringan klien dan server pada daftar yang sama (Interaction integrity: satu daftar, satu pengendali).

### 6.4 Laci keputusan [ada, dari `kaca/overlays.blade.php`]

```blade
<div x-show="drawerOpen" x-cloak class="fixed inset-0 z-40" role="presentation">
    <div class="absolute inset-0" style="background: var(--scrim)" @click="closeDrawer()" x-show="drawerOpen" x-transition.opacity.duration.200ms></div>
    <aside role="dialog" aria-modal="true" aria-labelledby="drawer-title" x-trap.noscroll="drawerOpen"
           x-show="drawerOpen"
           x-transition:enter="kaca-spring"
           x-transition:enter-start="opacity-0 translate-x-[108%] sm:translate-x-[108%] max-sm:translate-y-full max-sm:translate-x-0"
           x-transition:enter-end="opacity-100 translate-x-0 translate-y-0"
           x-transition:leave="transition duration-200 ease-in"
           x-transition:leave-start="opacity-100 translate-x-0 translate-y-0"
           x-transition:leave-end="opacity-0 translate-x-[108%] max-sm:translate-y-full max-sm:translate-x-0"
           class="glass-strong absolute inset-x-0 top-[7vh] bottom-0 flex flex-col overflow-hidden rounded-t-[26px] text-ink sm:top-2 sm:right-2 sm:bottom-2 sm:left-auto sm:w-[min(460px,calc(100vw-16px))] sm:rounded-[24px]">
        <h2 id="drawer-title" class="text-[21px] leading-tight font-black tracking-tight">…</h2>
        {{-- isi; aksi utama di kaki laci, satu tombol hijau --}}
    </aside>
</div>
```

Aturan: laci dan dialog selalu `.glass-strong` (kaca tebal, bukan kartu tipis); masuk dengan `kaca-spring`, keluar cepat `duration-200 ease-in`; `x-trap.noscroll` untuk fokus; Esc menutup; di bawah 640 px laci menjadi lembar bawah. Dasbor tidak mengambil keputusan: laci menautkan ke halaman keputusan bila ada (`item.url`), atau menulis alasan tertunda (`item.pending`).

### 6.5 Palet perintah Ctrl K [ada, khusus dasbor Admin]

Palet hidup di `kacaAdmin` (`openPalette()`, `parsed`, `rows`, `runRow()`, `onKey()`), markup di `overlays.blade.php`: dialog `.glass-strong` di `pt-[12vh]`, input `h-14` transparan, baris hasil `rounded-[12px]`, baris aktif `bg-primary-soft shadow-[inset_0_0_0_1px_var(--line-strong)]`, kaki `.mono` "↑↓ pindah · ↵ buka · Esc tutup". Pemicu di bilah atas adalah tombol `.glass` dengan `<kbd class="mono">Ctrl K</kbd>`.

Untuk layar lain, palet **belum** menjadi komponen bersama. Jangan menyalin `kacaAdmin` utuh; kalau sebuah layar butuh palet, usulkan dulu ekstraksi `kacaPalette` (lihat §12).

### 6.6 Grafik SVG [ada, pola dari `kaca/hourly.blade.php`]

```blade
<svg viewBox="0 0 {{ $W }} {{ $H }}" class="block h-auto w-full" role="img" aria-label="Grafik inspeksi per jam, penanda sekarang pukul 14.05">
    <defs>
        <pattern id="plan-hatch" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
            <rect width="6" height="6" fill="var(--c-plan)" /><line x1="0" y1="0" x2="0" y2="6" stroke="var(--hatch)" stroke-width="1.6" />
        </pattern>
    </defs>
    {{-- garis bantu: var(--line), putus-putus kecuali nol; label sumbu 13 px var(--meta) --}}
    <line x1="{{ $PL }}" x2="{{ $W - $PR }}" y1="{{ $y($v) }}" y2="{{ $y($v) }}" stroke="var(--line)" stroke-dasharray="2 4" />
    <text x="{{ $PL - 8 }}" y="{{ $y($v) + 4 }}" font-size="13" text-anchor="end" fill="var(--meta)">{{ $v }}</text>
    {{-- batang bertumpuk: pemisah var(--panel-strong), tumbuh sekali saat tampil --}}
    <rect x="…" y="…" width="…" height="…" rx="5" fill="var(--c-done)" stroke="var(--panel-strong)" stroke-width="1.5"
          style="transform-box: fill-box; transform-origin: bottom; animation: kaca-grow-y .7s var(--ease-hover) both; animation-delay: {{ $n * 40 }}ms" />
    {{-- penanda sekarang: satu, var(--accent), label putih --}}
</svg>
<div x-show="hover === {{ $n }}" x-cloak class="glass-strong pointer-events-none absolute top-8 z-10 w-48 rounded-[14px] p-3 text-[13px]">…</div>
```

Aturan: geometri dihitung di PHP (`$W`, `$H`, padding, skala) supaya HTML server sudah benar tanpa JS; warna lewat `var()`; teks SVG minimal 13 px; legenda di atas grafik sebagai baris teks + kotak warna (status tidak hanya warna); tooltip `.glass-strong`; seri "terjadwal" diarsir (`url(#plan-hatch)` / `.hatch-bg`); kategori memakai `var(--color-category-…)`. Donat: `.kaca-arc` dengan `--c` = keliling dan `--d` = jeda (lihat `panels.blade.php`).

### 6.7 Band dampak [ada, dari `kaca/panels.blade.php`]

```blade
@use('App\Support\DotMatrix')
@use('App\Support\Format')
<section class="impact-band relative overflow-hidden rounded-[24px] p-5 text-[#fef9c3] sm:p-7" aria-labelledby="impact-title">
    <img src="{{ asset('images/material/serbuk-kayu.jpg') }}" alt="" class="absolute inset-0 size-full object-cover opacity-[.14] mix-blend-luminosity">
    <div class="relative flex flex-wrap items-baseline justify-between gap-2">
        <h2 id="impact-title" class="text-[18px] font-extrabold">Dampak lingkungan tercatat</h2>
        <p class="text-[13px] text-[#e3d99a]">Total sejak platform berjalan{{ app()->environment('production') ? '' : ' · Data demo' }}</p>
    </div>
    @php($dm = DotMatrix::layout(Format::number($m['value'], $m['decimals']), 6, 2.2))
    <svg viewBox="0 0 {{ $dm['width'] }} {{ $dm['height'] }}" width="{{ $dm['width'] }}" height="{{ $dm['height'] }}" class="block max-w-full" role="img" aria-label="{{ Format::number($m['value'], $m['decimals']) }}">
        @foreach ($dm['dots'] as $d)
            <circle cx="{{ $d['x'] }}" cy="{{ $d['y'] }}" r="3" fill="{{ $d['on'] ? '#fef9c3' : 'rgba(254,249,195,.12)' }}" />
        @endforeach
    </svg>
</section>
```

`DotMatrix` hanya mengenal digit 0–9 dan koma; karakter lain dilewati. Angka matriks titik statis (tidak dihitung naik). Hex `#fef9c3` / `#e3d99a` di band adalah onForest dan secondary-label palet yang sudah ada (pengecualian tercatat di DESIGN.md); jangan menambah hex lain.

### 6.8 Empty state [ada]

```blade
<li class="grid place-items-center rounded-[16px] border border-dashed border-line-strong px-4 py-12 text-center">
    <p class="text-[16px] font-bold">Antrean bersih</p>
    <p class="mt-1 text-[14px] text-meta">Tidak ada keputusan yang menunggu Anda.</p>
    {{-- bila pengguna bisa membuat isinya: satu tombol utama, mis. <x-ui.button :href="…">Ajukan kebutuhan</x-ui.button> --}}
</li>
```

Bedakan "kosong sama sekali" dari "kosong karena saringan" ("Tidak ada di kategori ini" + cara kembali, ditampilkan dengan `x-show="visibleCount() === 0"`).

### 6.9 Form di atas kaca [pola]

```blade
{{-- Kolom dari DDL tabel categories (nama, tingkat_risiko); rute admin.categories.store sudah ada. --}}
<x-ui.card title="Kategori baru" class="lg:col-span-7">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">
        @csrf
        <x-ui.field label="Nama kategori" for="nama" name="nama" helper="Maksimal 100 karakter." required>
            <x-ui.input id="nama" name="nama" :value="old('nama')" maxlength="100" />
        </x-ui.field>
        <x-ui.field label="Tingkat risiko" for="tingkat_risiko" name="tingkat_risiko">
            <x-ui.select id="tingkat_risiko" name="tingkat_risiko">
                <option value="RENDAH">Rendah</option>
                <option value="TINGGI">Tinggi</option>
            </x-ui.select>
        </x-ui.field>
        <div class="flex justify-end gap-2 border-t border-line pt-4">
            <x-ui.button variant="secondary" :href="route('admin.categories.index')">Batal</x-ui.button>
            <x-ui.button type="submit">Simpan kategori</x-ui.button>
        </div>
    </form>
</x-ui.card>
```

Aturan form Kaca: kartu form adalah kaca (`x-ui.card`); **input, select, textarea, dan tombol tetap putih pekat** (`bg-surface`, batas `outline-input` #8a8350 3,86:1) — tidak pernah `bg-field` atau transparan, karena batas input harus ≥ 3:1 dan teks isian harus terbaca di atas latar apa pun; label 14 px tinta, helper 13 px meta, galat `text-danger` (di kaca = #7f1d1d); galat kredensial tetap satu `x-ui.alert` di atas form; aksi di kaki kartu dipisah `border-line`. Alert (`x-ui.alert`) tetap bidang putih/krem pekat — pesan, bukan kaca.

### 6.10 Tabel dan daftar di atas kaca [pola]

```blade
{{-- $rows: koleksi dari service; kolom tampil sesuai DDL layar Anda. Rute admin.categories.show sudah ada. --}}
<x-ui.card title="Kategori material" :padding="false">
    <ul class="divide-y divide-line px-4 sm:px-5">
        @foreach ($rows as $c)
            <li class="grid grid-cols-[1fr_auto] items-center gap-3 py-3" wire:key="cat-{{ $c->id_categories }}">
                <div class="min-w-0">
                    <p class="truncate text-[15px] font-bold">{{ $c->nama }}</p>
                    <p class="truncate text-[13px] text-meta">Toleransi susut {{ Format::number($c->toleransi_susut_persen, 1) }}%</p>
                </div>
                <x-ui.button variant="secondary" :href="route('admin.categories.show', $c)">Tinjau</x-ui.button>
            </li>
        @endforeach
    </ul>
</x-ui.card>
```

Aturan tabel Kaca: baris duduk **langsung di permukaan kaca kartu**, dipisah `divide-line` (garis rambut hijau 14%), bukan kotak putih per baris; baris yang bisa diklik memakai `hover:bg-primary-soft`; baris terpilih `bg-[var(--panel-strong)]`; header tabel 13 px meta; angka `tabular-nums` rata kanan; status tetap `x-ui.badge` (teks + glyph). Baris antrean keputusan yang berdiri sendiri boleh `.glass-row bg-field` (§6.3), daftar biasa tidak.

**Komponen bersama di Kaca:** `x-ui.table` (kait `data-table`) otomatis memakai garis `--line`; kolom pertama yang *sticky* bening di ≥ 1024 px dan menjadi alas `panel-strong` buram di < 1024 px, saat tabel bisa digeser. `x-ui.summary-strip` (kait `data-card` + `data-summary`) otomatis menjadi satu permukaan kaca dengan sel bening dan garis rambut per sel. `x-ui.kpi` dengan `tone="danger"` memakai `.text-error` yang di Kaca dipetakan ke #7f1d1d; nilainya 32 px / 900 (teks besar), jadi tidak perlu `.kaca-flag`. `tone="warning"` jangan dipakai di Kaca (warning sebagai teks hanya di permukaan pekat, §9).

### 6.11 Halaman detail [pola]

Susunan: kepala halaman (judul = objek, mis. "PO-1041") → grid `lg:grid-cols-12`: kartu utama kaca `lg:col-span-8` (fakta sebagai `dl` berpasangan label meta + nilai tinta, dipisah `divide-line`; riwayat status memakai pola `kaca-bar`/`river-stage`) + kartu aksi kaca `lg:col-span-4` (satu tombol utama hijau putih pekat, alasan bila nonaktif). Di bawah 1024 px kartu aksi turun ke bawah. Dialog konfirmasi = `.glass-strong` (§6.4). Foto material di kartu utama memakai label foto solid di atas scrim (DESIGN.md › Photos), bukan teks di atas kaca.

### 6.12 Isi yang terbuka di tempat [ada]

```blade
<button type="button" @click="open = !open" :aria-expanded="open.toString()" class="…">…</button>
<div class="kaca-spring grid" :class="open ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'" :inert="!open">
    <div class="overflow-hidden">…isi…</div>
</div>
```

`grid-template-rows` ada di daftar properti `.kaca-spring`, jadi lipatan memakai kurva spring. `:inert` menjaga isi tertutup dari Tab dan pembaca layar.

## 7. Penyedia data gaya AdminKaca

Ikuti `app/Services/Dashboard/AdminKaca.php`:

1. Satu kelas `final` di `app/Services/<Modul>/`, satu method publik `build(array $sumber, ?Carbon $now = null): array` yang mengembalikan array bentuk-view (kunci kecil, siap `Js::from`).
2. View tidak menjalankan query. Query di service; view hanya `@foreach` dan format (`App\Support\Format::number/rupiah/kg`).
3. Setiap bagian yang **tidak bisa dihitung dari DDL** diberi docblock yang menyebut kolom yang hilang, nilainya diambil dari contoh Lab, dan view menampilkan tanda **"Data demo"** di kartu yang sama:
   ```blade
   <span class="rounded-[6px] border border-dashed border-line-strong px-1.5 py-px text-[12px] text-meta">Data demo</span>
   ```
4. Yang tidak tercatat ditulis jujur: umur `null` → "–", riwayat kosong → "Riwayat status order ini belum tercatat.", tidak ada label "tertahan" tanpa ambang (lihat DESIGN.md › Data honesty).
5. Tombol ke halaman yang belum ada: `url => null` + `pending => 'Halaman … segera hadir'`; view merender elemen nonaktif dengan `title` berisi alasan.

## 8. Aturan gerak

| Gerak | Cara | Saat `prefers-reduced-motion: reduce` |
|---|---|---|
| Masuk/keluar laci, dialog, palet, panel | `x-transition:enter="kaca-spring"` + kelas start/end; keluar `transition duration-200 ease-in` | Hanya fade 200 ms (translate/scale dimatikan) |
| Lipatan isi baris | `.kaca-spring grid grid-rows-[0fr/1fr]` | Fade |
| Thumb segmen | `x-kaca-seg` (transisi CSS spring) | Pindah langsung |
| Saringan daftar, ganti tahap | `exit()` lalu `flip()` (Web Animations, kurva spring) | Fade 120–200 ms, tanpa geser |
| Kartu saat dimuat | `[data-rise]` + `--i` | Fade saja |
| Ubin, titik, busur, batang SVG | `.kaca-tile`, `.kaca-dot`, `.kaca-arc`, `kaca-grow-y` | Fade / busur tetap tergambar |
| Bilah lebar (progres, umur) | `transition-[width] duration-700` | Tetap berjalan (bukan gerak posisi) |
| Angka berganti karena aksi pengguna | hitung ke target (`countTo` di `kacaAdmin`) | Langsung ke nilai akhir |
| Latar melayang, denyut, arus, kilau | `.kaca-blob`, `.kaca-ping`, `.river-flow`, `.shimmer` | Berhenti |
| Tekan | `:active` scale .97 (otomatis) | Mati |
| Jeda gerak latar | Tombol `.kaca-still-toggle` di `<x-app.kaca>` menambah `.kaca-still`: latar berhenti di posisinya, denyut dan arus mati; pilihan disimpan per perangkat (`localStorage` `kaca-still`) | Tombol disembunyikan (gerak berulang sudah berhenti) |
| Hover warna/latar/bayangan | 150–200 ms `var(--ease-hover)` | Tetap |

Gerak berulang (latar melayang, denyut, arus) memenuhi WCAG 2.2.2 lewat tombol jeda di atas. Jangan menambah gerak berulang baru.

Prinsip: **satu kurva spring** (Lab: stiffness 420, damping 38, mass 0,9) untuk semua gerak tata letak — `--kaca-spring` di CSS, `SPRING` di JS. Jangan menulis `cubic-bezier` atau `ease-out` baru untuk gerak posisi. Angka tidak dihitung naik saat halaman dimuat. Gerak tereduksi mengikuti Lab (`MotionConfig reducedMotion="user"`): yang mati hanya posisi, skala, dan loop; fade, warna, lebar bilah, dan gambar donat tetap jalan, jadi tidak ada yang "meloncat".

## 9. Aturan kontras

**Diukur, bukan ditebak.** 25 Nov 2026, dasbor Admin dirender di Chrome dan setiap kotak teks diukur dengan rumus WCAG 2.x terhadap persentil 10 luminans latarnya (semua teks dibuat transparan untuk memotret latar). Putaran 1: 1440×900 di 5 posisi gulir dan 504×844 di 3 posisi (566 kotak). Putaran 2 setelah perbaikan: 1440×900 dengan latar melayang dihentikan di tiga waktu animasi berbeda (0, 17 dan 34 detik), karena posisi foto memengaruhi hasil (343 kotak).

| Teks | Permukaan | Rasio terendah terukur | Status |
|---|---|---|---|
| ink #1c2a0a | kaca | 6,17:1 | AA |
| meta #33330f | kaca | 5,18:1 | AA |
| ink #1c2a0a | tanah (kepala halaman, di luar kartu) | 5,21:1 (judul 26 px di 504 px: 4,64:1) | AA |
| hijau #2f3d12 (`.text-primary`) | kaca | 6,82:1 | AA |
| jingga #7c2d12 (`text-accent-text`) | kaca | 5,13:1 | AA |
| merah #7f1d1d (`text-danger`) | kaca polos | **4,33:1** pada satu posisi latar (4,68 pada posisi lain) | Tidak aman → wajib `.kaca-flag` |
| merah #7f1d1d | `.kaca-flag` | 6,93:1 | AA |
| merah #7f1d1d, label SVG dengan halo `panel-strong` | kaca | 5,85:1 (sebelumnya `var(--danger)` tanpa halo: 4,06:1) | AA |
| warning #92400e | baris `bg-field` | 6,28:1 | AA |
| merah #7f1d1d | baris `bg-field` | 9,03:1 | AA |
| meta #33330f | baris `bg-field` | 11,38:1 | AA |
| putih | lencana / tombol `bg-accent` | 5,18:1 | AA |
| putih | tombol `bg-primary` | 7,08:1 | AA |
| #e3d99a / #fef9c3 | band dampak | 7,47:1 / 12,24:1 | AA |
| tombol nonaktif (opacity 50–60%) | kaca / baris | 1,6–3,9:1 | Dikecualikan WCAG 1.4.3; wajib membawa alasan |

Batas teoretis (simulasi tanpa renderer, titik foto tergelap tepat di bawah stop kaca tertipis .36) jauh lebih pesimis: meta 4,54:1, tetapi teks berwarna turun ke 2,5–4,1:1. Karena itu aturan di bawah dibuat aman walau posisi kartu dan latar berubah:

1. **Teks di kaca polos** hanya: tinta, meta (#33330f), dan teks hijau/jingga versi Kaca (`text-primary` → #2f3d12, `text-accent-text` → #7c2d12) dalam potongan pendek tebal (≥ 13 px / 700). **Teks merah kecil di kaca polos selalu di dalam `.kaca-flag`** (`<span class="kaca-flag text-danger">19 hari</span>`); teks merah besar (≥ 24 px, mis. nilai `x-ui.kpi`) boleh tanpa alas. Label teks di SVG yang berwarna memakai halo: `stroke="var(--panel-strong)" stroke-width="4" stroke-linejoin="round" paint-order="stroke"`. Kalimat panjang berwarna tidak boleh di kaca polos.
2. **`text-warning`, `text-info`, `text-accent` (#c2410c), dan `var(--danger)` sebagai warna teks** hanya di permukaan yang lebih pekat: baris `bg-field`, `--panel-strong`, atau `.glass-strong`. Di kaca polos mereka hanya boleh sebagai isi (titik, batang, lencana).
3. **Teks di luar kartu** (kepala halaman, remah roti) hanya tinta. Meta #4a4a1a di tanah hanya 3,0–3,2:1 di atas foto tergelap (audit kontras 25 Nov 2026, dicatat di komentar `dashboard-kaca.css`) — itu sebabnya `.dash-kaca h1 + p` dan `.dash-eyebrow` dipaksa tinta.
4. **Kontrol** (tombol, input, select) putih pekat atau hijau terisi; jangan transparan.
5. **Jangan menurunkan** alpha kartu (.66/.36/.50), kerudung (.52/.22/.42), atau mengganti foto latar tanpa mengukur ulang.
6. Elemen nonaktif (opacity 50–60%) dikecualikan WCAG 1.4.3, tetapi wajib membawa alasan (`title` atau teks di dekatnya).

Cara mengukur layar baru: Lampiran A.

## 10. Daftar periksa sebelum PR

- [ ] Isi halaman dibungkus `<x-app.kaca>` (§5); sidebar ikut jadi kaca; tombol jeda gerak tampil dan berfungsi.
- [ ] Kartu memakai `x-ui.card` atau `.glass`; laci/dialog/tooltip `.glass-strong`; tidak ada kartu putih tersisa kecuali alert dan kontrol.
- [ ] Input, select, textarea, tombol putih pekat atau hijau terisi; satu tombol hijau per wilayah.
- [ ] Tidak ada hex baru; warna dari kelas token atau `var(--…)` di §4. Teks merah memakai `text-danger`/`var(--color-danger)`, bukan `var(--danger)`, dan di kaca polos duduk di `.kaca-flag`; label SVG berwarna berhalo.
- [ ] Teks berwarna di kaca polos hanya versi Kaca dan pendek tebal; warning/info/accent sebagai teks hanya di `bg-field`/`panel-strong`/`glass-strong` (§9).
- [ ] Teks di luar kartu hanya tinta.
- [ ] Tidak ada pill di isi (kecuali tanda "Data demo" dan lencana hitungan); status = teks + glyph.
- [ ] Data dari service bergaya `AdminKaca`; bagian yang tidak didukung DDL bertanda "Data demo" di kartunya; tidak ada label diagnosis tanpa ambang.
- [ ] Gerak: spring dari `--kaca-spring`/`flip()`; diuji dengan klik cepat berulang (tidak meloncat); diuji ulang dengan *prefers-reduced-motion: reduce* (posisi mati, fade jalan).
- [ ] Kontras diukur pada render nyata di 1440 dan ≤ 504 px, beberapa posisi gulir, dan dua atau lebih waktu animasi latar; hasil ditulis di PR. Yang tidak sempat diukur disebut.
- [ ] `forced-colors: active` (Windows kontras tinggi): kartu kembali putih, latar hilang, teks terbaca.
- [ ] 360 px: tidak ada gulir horizontal halaman; grafik lebar menggulir di dalam kartunya (`-mx-4 overflow-x-auto px-4`).
- [ ] Keyboard: semua kontrol tercapai Tab, fokus terlihat (ring jingga 2 px), Esc menutup laci/dialog, `x-trap` di overlay.
- [ ] Test fitur menegaskan `dash-kaca` dan `kaca-backdrop` hadir di layar itu, urutan judul kartu, dan "Data demo" bila ada contoh; asersi lama `assertDontSee('dash-kaca')` untuk layar itu diperbarui.
- [ ] `npm run build` berhasil (kelas arbitrer `x-transition` terbentuk); `php artisan test`, Pint, Larastan bersih.
- [ ] Skill `visual-qa` dijalankan; screenshot dibuka dan dinilai.

## 11. Jebakan yang sudah ditemukan

1. **`var(--danger)` vs `text-danger`.** Di dalam `.dash-kaca`, `--danger` = #b91c1c (isi), sedangkan `--color-danger` (kelas `text-danger`) = #7f1d1d (teks). Label keluar Sungai Order dulu memakai `fill="var(--danger)"` dan terukur 4,06:1; sudah diganti `var(--color-danger)` + halo (5,85:1). Hal yang sama berlaku untuk `--accent` (isi) vs `--color-accent-text` (teks). **Merah tua pun tidak cukup di kaca polos** pada semua posisi latar (4,33:1 terukur), karena itu `.kaca-flag`.
2. **`.glass` memaksa radius 22 px.** Untuk tombol/kapsul kaca kecil di bilah atas, timpa dengan `rounded-[14px]!` (tanda `!` Tailwind 4), seperti di `kaca/header.blade.php`.
3. **Gerak tereduksi di Windows.** PC dengan "Animation effects" mati melaporkan `prefers-reduced-motion: reduce`. Jangan mematikan semua transisi (`transition: none` gaya Senja) — itu membuat semuanya meloncat. Kebijakan Kaca hanya mematikan posisi/skala/loop. `dashboard-senja.css` sengaja memakai `.dash-senja:not(.dash-kaca) *` untuk aturan `transition: none`-nya.
4. **Transparansi Windows.** `prefers-reduced-transparency` sengaja **tidak** diikuti (keputusan PM). Jangan menambah aturan media itu; keterbacaan dijaga lantai alpha dan aturan §9.
5. **`x-kaca-seg` hanya bereaksi pada `aria-pressed`.** Tombol tanpa `:aria-pressed` membuat thumb tidak bergerak. Thumb diposisikan ulang saat resize dan setelah font termuat.
6. **`flip()` butuh fungsi, bukan array.** `items` dipanggil dua kali (sebelum dan sesudah perubahan). Elemen tersembunyi (`display: none`) dilewati.
7. **Animasi yang tertahan di tab latar.** `exit()` dan `kacaRiver` memakai batas waktu (`settled`) supaya state tidak macet ketika browser menahan linimasa animasi; pertahankan pola itu di komponen baru.
8. **Kelas arbitrer di `x-transition`** (`translate-x-[108%]`, `scale-[.97]`) hanya terbentuk bila tertulis utuh di Blade; jangan menyusunnya dari string dinamis.
9. **`body:has(.dash-kaca)` membuat body bening.** Latar krem datang dari `<html>`. Jangan memberi `bg-background` pada pembungkus isi; latar foto akan tertutup.
10. **Komponen putih lama.** `x-ui.table` dan `x-ui.summary-strip` sudah punya varian Kaca otomatis (§6.10). Komponen lain yang memakai `bg-surface` sebagai kartu (bukan kontrol) harus diberi `data-card` atau diganti `x-ui.card`.
11. **`kacaAdmin` bukan komponen umum.** Inisialisasinya membaca `cfg.periods['Hari ini']`; dipakai di layar tanpa data itu, Alpine gagal.
12. **Data demo di produksi.** Kepala dasbor dan band dampak menyembunyikan tanda demo di `production`; panel yang isinya contoh (strip periode, peta) menampilkan tanda di semua lingkungan. Jangan menyembunyikan tanda pada data yang memang contoh.
13. **`@if` di akar komponen Livewire.** Livewire menempelkan `wire:id` ke tag pertama yang ditemukannya. `@if` di akar menghasilkan penanda `<!--[if BLOCK]>` sehingga `wire:id` jatuh ke elemen anak (pernah terjadi: ke `.kaca-backdrop`). Elemen akar harus selalu satu tag tanpa `@if` di depannya; `<x-app.kaca>` menangani ini dengan menyusun atribut di `@php`.
14. **Teks redup komponen lama.** Di dalam `.dash-kaca`, `text-on-surface-muted`/`-subtle` otomatis menjadi #33330f dan `.text-error`/`.text-success` menjadi versi gelap. Jangan menimpa kembali dengan hex.

## 12. Keputusan 25 Nov 2026 (diambil atas delegasi PM)

| Soal | Keputusan | Alasan |
|---|---|---|
| Pembungkus Kaca | Komponen bersama `<x-app.kaca>` dipasang per layar; **tidak** di `layouts/app.blade.php`. | Satu sumber markup latar dan foto (kontras diukur di atasnya); layar Senja yang belum dibangun ulang tidak rusak; tidak menyentuh hot spot layout. |
| `x-ui.table`, `x-ui.summary-strip` | Varian Kaca otomatis lewat kait `data-table` / `data-card` + `data-summary`; tampilan di luar Kaca tidak berubah. | Satu komponen untuk kedua material; layar yang dibangun ulang tidak perlu markup tabel sendiri. |
| Teks redup dan semantik di komponen lama | Di dalam `.dash-kaca`: `--color-on-surface-muted/-subtle` → #33330f, `.text-error` → #7f1d1d, `.text-success` → #2f3d12. | Semua `x-ui.*` langsung terbaca di kaca tanpa kelas khusus. |
| Teks merah di kaca polos | Selalu `.kaca-flag` (alas `bg-field`, radius 6 px); label SVG berwarna diberi halo. | Terukur 4,33:1 pada posisi latar tertentu; dengan alas 6,93:1. Bentuknya sama dengan tag ID, bukan pill. |
| Gerak berulang (WCAG 2.2.2) | Tombol jeda 44 px di kanan bawah setiap layar Kaca, pilihan disimpan per perangkat; tersembunyi saat *reduced motion*. | Memenuhi 2.2.2 tanpa menghapus gerak yang disukai PM. |
| Teks 11 px di batang riwayat | Dinaikkan ke 12 px (lebar minimum batang 40 px). | Batas minimum 12 px. |
| Dasbor Seller, Buyer, Inspector | Tetap struktur "Tiga pertanyaan" (DESIGN.md › Layout), dibangun ulang dengan material Kaca; susunan ala Lab hanya untuk Admin. | Isi tiap peran sudah disetujui per peran pada 22 Nov; Lab hanya punya rancangan Admin. |
| Login, lupa sandi, reset sandi | Panel foto + overlay hutan di kiri tetap; sisi form memakai latar Kaca (`<x-app.kaca>`) dan form di kartu kaca. Landing tidak diubah (permukaan publik). | Panel foto adalah identitas yang sudah ada; form tetap konsisten dengan aplikasi. |
| Palet Ctrl K dan laci untuk layar lain | Tetap di `kacaAdmin` sampai ada layar kedua yang membutuhkannya; saat itu diekstrak menjadi komponen bersama, bukan disalin. | Tidak membuat abstraksi sebelum ada pemakai kedua. |

Yang masih harus dikerjakan (bukan keputusan): membangun ulang layar selain dasbor Admin dengan `<x-app.kaca>`, dan memperbarui asersi `assertDontSee('dash-kaca')` di `tests/Feature/DashboardTest.php` saat dasbor Seller dibangun ulang.

## Lampiran A. Mengukur kontras di atas kaca

Kaca tidak punya satu warna latar, jadi rasio dari dua hex tidak cukup. Ukur pada render nyata (Chrome lewat chrome-devtools MCP atau panel browser), di 1440 px dan ≤ 504 px, minimal tiga posisi gulir:

1. Buka layar, jeda latar dan animasi masuk, lalu kumpulkan kotak teks (jalankan di konsol halaman; simpan hasilnya sebagai `boxes_N.json`):

```js
(() => {
  const st = document.createElement('style');
  st.textContent = '.kaca-blob,.dash-kaca *{animation-play-state:paused!important}.dash-kaca [data-rise]{animation:none!important;opacity:1!important}';
  document.head.appendChild(st);
  const root = document.querySelector('.dash-kaca'), out = [];
  const surf = (el) => (el.closest('.glass-strong, .impact-band, .glass-row, [data-card], .glass')?.className.match(/glass-strong|impact-band|glass-row|glass/)?.[0]) ?? 'tanah';
  const w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
  for (let n; (n = w.nextNode());) {
    const el = n.parentElement;
    if (!n.textContent.trim() || el.closest('[x-cloak], .sr-only, .kaca-backdrop')) continue;
    const cs = getComputedStyle(el);
    let op = 1; for (let p = el; p && p !== root; p = p.parentElement) op *= +getComputedStyle(p).opacity;
    if (op < 0.15 || cs.visibility === 'hidden') continue;           // isi terlipat / tersembunyi
    const r = document.createRange(); r.selectNodeContents(n);
    for (const b of r.getClientRects()) if (b.bottom > 0 && b.top < innerHeight && b.width > 2)
      out.push({ t: n.textContent.trim().slice(0, 40), x: b.left, y: b.top, w: b.width, h: b.height,
        color: el instanceof SVGElement ? cs.fill : cs.color, op, fs: parseFloat(cs.fontSize), fw: +cs.fontWeight, surf: surf(el) });
  }
  return JSON.stringify(out);
})();
```

Ulangi langkah 1–3 pada dua atau lebih waktu animasi latar, karena posisi foto memengaruhi hasil (teks merah pernah lolos di satu posisi dan gagal di posisi lain):

```js
for (const b of document.querySelectorAll('.kaca-blob')) for (const a of b.getAnimations()) { a.pause(); a.currentTime = 17000; } // juga 0 dan 34000
```

2. Sembunyikan teks dan potret viewport yang sama (`bg_N.png`). Halo label SVG (`stroke`) sengaja tidak disembunyikan: halo adalah latar langsung teks itu.

```js
document.head.insertAdjacentHTML('beforeend', '<style>.dash-kaca *{color:transparent!important;-webkit-text-fill-color:transparent!important}.dash-kaca svg text{fill:transparent!important}.dash-kaca img{visibility:hidden!important}</style>');
```

3. Ukur dengan Python (`python -I`, Pillow): untuk tiap kotak, latar = persentil 10 luminans piksel di kotak itu (skala koordinat dengan `lebar_png / innerWidth`); warna teks rgba dikomposit dengan opasitas leluhur; rasio WCAG 2.x `(L1 + 0,05) / (L2 + 0,05)` dengan linearisasi sRGB (`c/12,92` bila `c ≤ 0,04045`, selain itu `((c + 0,055)/1,055)^2,4`). Ambang 4,5:1, atau 3:1 untuk teks ≥ 24 px atau ≥ 18,66 px tebal ≥ 700. Laporkan rasio terendah per pasangan (permukaan × warna) dan daftar yang gagal; elemen nonaktif dicatat terpisah.
