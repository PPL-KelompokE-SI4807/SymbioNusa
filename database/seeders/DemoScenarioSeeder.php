<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\DocumentVerification;
use App\Models\ImpactFactor;
use App\Models\InspectorProfile;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/**
 * Data simulasi untuk server demo (OCI VM): beberapa bulan aktivitas pasar limbah industri dengan semua status
 * order terisi, sehingga setiap dasbor, daftar, dan antrean punya isi. Semua tanggal relatif terhadap hari
 * dijalankan, jadi dasbor tetap terlihat "hidup". Angka dampak dihitung dari faktor dampak (PRD §4.5), bukan
 * dikarang per baris. Data ini fiktif: nama perusahaan, orang, NIB, dan plat nomor bukan data nyata.
 *
 * Dijalankan sekali: bila perusahaan penanda sudah ada, seeder berhenti tanpa menulis apa pun.
 * Bergantung pada SymbioNusaDemoSeeder (akun dasar admin/seller/buyer/inspector@symbionusa.test).
 */
class DemoScenarioSeeder extends Seeder
{
    /** NIB perusahaan pertama; keberadaannya menandai data simulasi sudah pernah dimuat. */
    public const MARKER_NIB = '9120000000001';

    private const PASSWORD = 'password';

    /** Alur normal order (03_STATUS_MAP). */
    private const CHAIN = [
        'DRAFT', 'MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'INSPEKSI', 'LOLOS_INSPEKSI',
        'INVOICED', 'DIBAYAR', 'DALAM_PENGIRIMAN', 'DITERIMA', 'SELESAI',
    ];

    /** Jalur keluar dan tahap terakhir sebelum keluar (sesuai OrderStateMachine::TRANSITIONS). */
    private const EXIT_FROM = [
        'DITOLAK' => 'MENUNGGU_KONFIRMASI',
        'DIBATALKAN' => 'DIKONFIRMASI',
        'GAGAL_INSPEKSI' => 'INSPEKSI',
        'SENGKETA' => 'DITERIMA',
    ];

    private const REASONS = [
        'DRAFT' => 'PO dibuat oleh Buyer dari RFQ yang disepakati',
        'MENUNGGU_KONFIRMASI' => 'PO dikirim ke Seller untuk konfirmasi',
        'DIKONFIRMASI' => 'PO dikonfirmasi Seller, stok direservasi',
        'INSPEKSI' => 'Inspector mulai memeriksa mutu dan dokumen',
        'LOLOS_INSPEKSI' => 'Hasil inspeksi final: lulus',
        'GAGAL_INSPEKSI' => 'Hasil inspeksi final: kadar air di atas batas kategori',
        'INVOICED' => 'Invoice diterbitkan',
        'DIBAYAR' => 'Bukti transfer diverifikasi Admin',
        'DALAM_PENGIRIMAN' => 'Armada berangkat dengan manifes',
        'DITERIMA' => 'Barang diterima dan ditimbang di gudang Buyer',
        'SELESAI' => 'Order ditutup, dampak dicatat',
        'SENGKETA' => 'Buyer mengajukan sengketa',
        'DIBATALKAN' => 'Dibatalkan atas permintaan Buyer',
        'DITOLAK' => 'Seller menolak: stok sudah dialokasikan ke pembeli lain',
    ];

    /** @var array<string, Category> */
    private array $categories = [];

    /** @var array<string, Company> */
    private array $companies = [];

    /** @var array<string, User> key perusahaan atau peran => user utama */
    private array $users = [];

    /** @var list<User> */
    private array $inspectors = [];

    private User $admin;

    private Carbon $now;

    private int $manifestSeq = 0;

    public function run(): void
    {
        if (Company::where('nib', self::MARKER_NIB)->exists()) {
            $this->command?->warn('Data simulasi sudah ada (NIB '.self::MARKER_NIB.'), dilewati. Pakai migrate:fresh --seed untuk memuat ulang.');

            return;
        }

        $base = User::where('email', 'admin@symbionusa.test')->first();
        if (! $base) {
            $this->call(SymbioNusaDemoSeeder::class);
        }

        mt_srand(20261126);
        $this->now = Carbon::now();

        DB::transaction(function () {
            $this->admin = User::where('email', 'admin@symbionusa.test')->firstOrFail();
            $this->seedCategories();
            $this->seedCompanies();
            $this->seedInspectors();
            $listings = $this->seedListings();
            $this->seedDemandRequests();
            $this->seedOpenRfqs($listings);
            $this->seedOrders($listings);
            $this->syncStock();
            $this->seedGovernance();
        });

        $this->command?->info('Data simulasi dimuat: '.PurchaseOrder::count().' PO, '.Listing::count().' listing, '.Company::count().' perusahaan.');
    }

    // ---------------------------------------------------------------- master data

    private function seedCategories(): void
    {
        $spec = fn (array $fields) => array_map(fn ($f) => ['nama' => $f[0], 'tipe' => $f[1], 'satuan' => $f[2], 'wajib' => $f[3]], $fields);

        $rows = [
            'plastik' => ['Plastik PP/PE Non-B3', 2.50, 5.00, 1.00, 'RENDAH', 0, $spec([['Kadar Air', 'number', '%', true], ['Warna', 'text', '', true], ['Bentuk', 'text', '', false]]),
                [1.0500, 1.9500, 0.4200, 0.1050, 0.85, 17500, 650]],
            'logam' => ['Logam Aluminium Scrap', 1.00, 2.00, 0.50, 'RENDAH', 0, $spec([['Kemurnian', 'number', '%', true], ['Jenis Paduan', 'text', '', false]]),
                [0.3000, 11.500, 0.6000, 0.1050, 0.90, 38000, 400]],
            'pet' => ['Plastik PET Botol Cacah', 2.00, 3.00, 1.00, 'RENDAH', 0, $spec([['Kadar Air', 'number', '%', true], ['Warna', 'text', '', true], ['Ukuran Cacahan', 'number', 'mm', false]]),
                [1.1000, 2.1500, 0.4500, 0.1050, 0.80, 16000, 650]],
            'kertas' => ['Kertas & Kardus Bekas', 3.00, 12.00, 2.00, 'RENDAH', 0, $spec([['Kadar Air', 'number', '%', true], ['Grade', 'text', '', true]]),
                [0.9500, 1.3000, 0.3500, 0.1050, 0.90, 4800, 300]],
            'kayu' => ['Serbuk Kayu Gergaji', 4.00, 20.00, 3.00, 'RENDAH', 0, $spec([['Kadar Air', 'number', '%', true], ['Jenis Kayu', 'text', '', false], ['Ukuran Partikel', 'number', 'mm', false]]),
                [0.6200, 0.9000, 0.1500, 0.1050, 0.75, 1200, 150]],
            'tahu' => ['Ampas Tahu Basah', 6.00, 80.00, 5.00, 'TINGGI', 1, $spec([['Kadar Air', 'number', '%', true], ['Umur Sejak Produksi', 'number', 'jam', true]]),
                [0.7000, 0.8500, 0.1000, 0.1050, 0.70, 2500, 200]],
            'kain' => ['Kain Perca Tekstil', 3.00, 8.00, 2.00, 'RENDAH', 0, $spec([['Komposisi Serat', 'text', '', true], ['Kadar Air', 'number', '%', false]]),
                [1.4000, 3.2000, 0.5500, 0.1050, 0.60, 9000, 500]],
        ];

        foreach ($rows as $key => [$nama, $susut, $air, $tolAir, $risiko, $lab, $schema, $f]) {
            $category = Category::firstOrCreate(['nama' => $nama], [
                'is_b3_prohibited' => 0, 'toleransi_susut_persen' => $susut, 'kadar_air_maks_persen' => $air,
                'toleransi_kadar_air_poin' => $tolAir, 'tingkat_risiko' => $risiko, 'wajib_sertifikat_lab' => $lab, 'status' => 'AKTIF',
            ]);
            if ($category->spec_schema === null) {
                $category->update(['spec_schema' => $schema]);
            }
            $this->categories[$key] = $category;

            // Versi 1 (lama, nonaktif) dan versi 2 (aktif) agar riwayat versi faktor terlihat.
            foreach ([1 => [0.92, 0], 2 => [1.0, 1]] as $versi => [$scale, $aktif]) {
                ImpactFactor::firstOrCreate(
                    ['categories_id_categories' => $category->id_categories, 'versi' => $versi],
                    [
                        'ef_buang' => round($f[0] * $scale, 4), 'ef_virgin' => round($f[1] * $scale, 4), 'ef_proses' => $f[2],
                        'ef_trans' => $f[3], 'substitution_rate' => $f[4], 'harga_virgin_ref' => $f[5], 'biaya_buang_ref' => $f[6],
                        'berlaku_dari' => $this->now->copy()->subMonths($versi === 1 ? 14 : 5)->startOfMonth()->toDateString(),
                        'aktif' => $aktif,
                    ]
                );
            }
        }

        // Kategori terlarang (B3) dan kategori arsip: untuk menguji penolakan listing dan filter status.
        Category::firstOrCreate(['nama' => 'Oli Bekas (B3)'], [
            'is_b3_prohibited' => 1, 'toleransi_susut_persen' => 0, 'tingkat_risiko' => 'TINGGI', 'wajib_sertifikat_lab' => 1, 'status' => 'AKTIF',
        ]);
        Category::firstOrCreate(['nama' => 'Kaca Cullet Campur'], [
            'is_b3_prohibited' => 0, 'toleransi_susut_persen' => 1.50, 'tingkat_risiko' => 'RENDAH', 'wajib_sertifikat_lab' => 0, 'status' => 'ARSIP',
        ]);
    }

    private function seedCompanies(): void
    {
        // key => [nama, tipe, nib, alamat, kontak, lat, lng, status, [nama user, email user]]
        $rows = [
            'plastindo' => ['PT Plastindo Jaya Abadi', 'SELLER', self::MARKER_NIB, 'Kawasan MM2100 Blok C-7, Cikarang Barat, Bekasi', '0218990211', -6.3046, 107.0631, 'TERVERIFIKASI', ['Hendra Wijaya', 'hendra@plastindo.test']],
            'kayu' => ['CV Kayu Lestari Jepara', 'SELLER', '9120000000002', 'Jl. Raya Tahunan Km 4, Jepara', '0291592033', -6.6110, 110.6890, 'TERVERIFIKASI', ['Sri Wahyuni', 'sri@kayulestari.test']],
            'tahu' => ['UD Tahu Sumedang Berkah', 'SELLER', '9120000000003', 'Jl. Prabu Geusan Ulun 88, Sumedang', '0261201554', -6.8580, 107.9200, 'TERVERIFIKASI', ['Asep Saepudin', 'asep@tahuberkah.test']],
            'garmen' => ['PT Garmen Sentosa Bandung', 'SELLER', '9120000000004', 'Jl. Moh. Toha Km 6, Kab. Bandung', '0225204411', -6.9790, 107.6220, 'TERVERIFIKASI', ['Lina Marlina', 'lina@garmensentosa.test']],
            'kemasan' => ['PT Kemasan Kertas Surya', 'SELLER', '9120000000005', 'Jl. Raya Buduran 21, Sidoarjo', '0318961177', -7.4290, 112.7230, 'TERVERIFIKASI', ['Bambang Hartono', 'bambang@kemasansurya.test']],
            'alumindo' => ['PT Alumindo Cast Pasuruan', 'SELLER', '9120000000006', 'PIER Pasuruan Blok D-3', '0343631208', -7.6220, 112.8400, 'TERVERIFIKASI', ['Yohanes Kurniawan', 'yohanes@alumindo.test']],
            'biomassa' => ['PT Biomassa Energi Nusantara', 'BUYER', '9120000000007', 'Jl. Raya Anyer Km 12, Cilegon', '0254311908', -6.0170, 106.0300, 'TERVERIFIKASI', ['Rizky Ramadhan', 'rizky@biomassa.test']],
            'pakan' => ['CV Pakan Ternak Makmur', 'BUYER', '9120000000008', 'Jl. Raya Pagaden 17, Subang', '0260411320', -6.4700, 107.8100, 'TERVERIFIKASI', ['Dedi Kusnadi', 'dedi@pakanmakmur.test']],
            'kertasdaur' => ['PT Kertas Daur Hijau', 'BUYER', '9120000000009', 'Jl. Raya Ngoro Industri 5, Mojokerto', '0321619744', -7.5600, 112.6200, 'TERVERIFIKASI', ['Maya Anggraini', 'maya@kertasdaur.test']],
            'bantal' => ['PT Isi Bantal Prima', 'BUYER', '9120000000010', 'Jl. Solo–Sragen Km 9, Karanganyar', '0271825603', -7.5600, 110.8800, 'TERVERIFIKASI', ['Wahyu Nugroho', 'wahyu@isibantal.test']],
            'polimer' => ['PT Recycle Polimer Indonesia', 'BUYER', '9120000000011', 'Jl. Industri Raya III, Jatiuwung, Tangerang', '0215902218', -6.1900, 106.5700, 'TERVERIFIKASI', ['Fransiska Halim', 'fransiska@recyclepolimer.test']],
            'sirkular' => ['PT Sirkular Jaya Mandiri', 'KEDUANYA', '9120000000012', 'Kawasan Industri Wijayakusuma, Semarang', '0248662190', -6.9740, 110.3320, 'TERVERIFIKASI', ['Galih Prasetyo', 'galih@sirkularjaya.test']],
            'pending1' => ['CV Limbah Kain Nusantara', 'SELLER', '9120000000013', 'Jl. Raya Majalaya 45, Kab. Bandung', '0225950312', -7.0480, 107.7540, 'PENDING', ['Nining Suryani', 'nining@limbahkain.test']],
            'pending2' => ['PT Agro Pellet Sejahtera', 'BUYER', '9120000000014', 'Jl. Lintas Timur Km 8, Lampung Selatan', '0721780045', -5.6100, 105.3200, 'PENDING', ['Ilham Saputra', 'ilham@agropellet.test']],
            'ditolak' => ['UD Rongsok Maju', 'SELLER', '9120000000015', 'Jl. Pasar Baru 3, Karawang', '0267401188', -6.3060, 107.3000, 'DITOLAK', ['Tono Sutrisno', 'tono@rongsokmaju.test']],
        ];

        foreach ($rows as $key => [$nama, $tipe, $nib, $alamat, $kontak, $lat, $lng, $status, [$userNama, $email]]) {
            $company = Company::create([
                'nama' => $nama, 'tipe' => $tipe, 'nib' => $nib, 'alamat' => $alamat, 'kontak' => $kontak,
                'lat' => $lat, 'lng' => $lng, 'tos_versi' => '1.0',
                'tos_disetujui_pada' => $this->now->copy()->subDays(mt_rand(20, 200)),
                'status_verifikasi' => $status,
            ]);
            $this->companies[$key] = $company;
            $this->users[$key] = User::create([
                'nama' => $userNama, 'email' => $email, 'role' => $tipe === 'BUYER' ? 'BUYER' : 'SELLER',
                'password_hash' => Hash::make(self::PASSWORD), 'status' => $status === 'DITOLAK' ? 'NONAKTIF' : 'AKTIF',
                'id_company' => $company->id_company,
            ]);
            $this->companyDocuments($company, $status);
        }

        // Perusahaan dari SymbioNusaDemoSeeder ikut bertransaksi supaya akun dasar juga melihat data.
        foreach (['seller' => 'seller@symbionusa.test', 'buyer' => 'buyer@symbionusa.test'] as $key => $email) {
            $user = User::where('email', $email)->firstOrFail();
            $company = $user->company;
            $company->update(['lat' => $key === 'seller' ? -6.2850 : -6.3220, 'lng' => $key === 'seller' ? 107.1700 : 107.3370]);
            $this->companies[$key] = $company;
            $this->users[$key] = $user;
        }
        $pending = Company::where('nib', '5555666677778')->first();
        if ($pending) {
            $this->companyDocuments($pending, 'PENDING');
        }
    }

    private function companyDocuments(Company $company, string $status): void
    {
        $docs = ['NIB', 'NPWP', 'IZIN_LINGKUNGAN'];
        if (in_array($company->tipe, ['SELLER', 'KEDUANYA'], true)) {
            $docs[] = 'SURAT_NON_B3';
        }

        foreach ($docs as $i => $jenis) {
            $docStatus = match ($status) {
                'TERVERIFIKASI' => 'VALID',
                'DITOLAK' => $jenis === 'IZIN_LINGKUNGAN' ? 'KEDALUWARSA' : 'VALID',
                default => 'PENDING',
            };
            $berlaku = $jenis === 'IZIN_LINGKUNGAN'
                ? ($docStatus === 'KEDALUWARSA' ? $this->now->copy()->subMonths(3) : $this->now->copy()->addMonths(mt_rand(4, 30)))
                : null;

            DB::table('company_documents')->insert([
                'jenis' => $jenis,
                'file_url' => $this->placeholder('public', "company-documents/{$company->id_company}/".strtolower($jenis).'.pdf', "{$jenis} {$company->nama}"),
                'berlaku_sampai' => $berlaku?->toDateString(),
                'status' => $docStatus,
                'id_company' => $company->id_company,
            ]);
        }
    }

    private function seedInspectors(): void
    {
        $today = InspectorProfile::today();
        $c = fn (string ...$keys) => array_map(fn ($k) => $this->categories[$k]->id_categories, $keys);

        $rows = [
            ['dewi.inspector@symbionusa.test', 'Dewi Lestari, S.T.', $c('kertas', 'kayu', 'logam'), 'Jawa Timur', 'INS-KRT-2025-031', $today->copy()->addMonths(20)],
            ['agus.inspector@symbionusa.test', 'Agus Pratama', $c('pet', 'plastik', 'kain'), 'DKI Jakarta & Banten', 'INS-PLS-2026-002', $today->copy()->addMonths(11)],
            ['nurul.inspector@symbionusa.test', 'Nurul Hidayah, M.Si.', $c('tahu', 'kayu', 'kain'), 'Jawa Tengah', 'INS-ORG-2024-019', $today->copy()->addDays(5)],
            ['yudi.inspector@symbionusa.test', 'Yudi Firmansyah', $c('plastik'), 'Jawa Barat', 'INS-PLS-2023-008', $today->copy()->addMonths(6)],
        ];

        foreach ($rows as $i => [$email, $nama, $keahlian, $wilayah, $noSertifikat, $berlaku]) {
            $user = User::create([
                'nama' => $nama, 'email' => $email, 'role' => 'INSPECTOR',
                'password_hash' => Hash::make(self::PASSWORD), 'status' => $i === 3 ? 'NONAKTIF' : 'AKTIF',
            ]);
            InspectorProfile::create([
                'bidang_keahlian' => $keahlian, 'wilayah_tugas' => $wilayah, 'no_sertifikat' => $noSertifikat,
                'sertifikat_url' => $this->placeholder('local', "inspectors/certificates/{$noSertifikat}.pdf", "Sertifikat {$noSertifikat}"),
                'berlaku_sampai' => $berlaku->toDateString(), 'status_aktif' => $i === 3 ? 0 : 1,
                'id_user' => $user->id_user,
            ]);
        }

        // Inspector yang boleh ditugaskan: profil aktif dan sertifikat masih berlaku.
        $this->inspectors = User::whereHas('inspectorProfile', fn ($q) => $q->assignable())->with('inspectorProfile')->get()->all();
    }

    // ---------------------------------------------------------------- pasar

    /**
     * @return list<Listing>
     */
    private function seedListings(): array
    {
        // [penjual, kategori, judul, spesifikasi, volume, harga, biaya buang, metode lama, status]
        $rows = [
            ['plastindo', 'plastik', 'Afval Plastik PP Injeksi Warna Campur', ['Kadar Air' => '1.8', 'Warna' => 'campur', 'Bentuk' => 'gilingan'], 24000, 6200, 650, 'Dibakar terbuka', 'PUBLISHED'],
            ['plastindo', 'pet', 'Cacahan Botol PET Bening Grade A', ['Kadar Air' => '0.9', 'Warna' => 'bening', 'Ukuran Cacahan' => '12'], 18000, 8900, 700, 'Dijual ke pengepul', 'PUBLISHED'],
            ['plastindo', 'plastik', 'Film LDPE Bekas Kemasan Bahan Baku', ['Kadar Air' => '3.5', 'Warna' => 'natural'], 9000, 4100, 600, 'Landfill', 'DRAF'],
            ['kayu', 'kayu', 'Serbuk Gergaji Jati & Mahoni', ['Kadar Air' => '14', 'Jenis Kayu' => 'jati, mahoni', 'Ukuran Partikel' => '2'], 60000, 450, 150, 'Dibakar di tungku', 'PUBLISHED'],
            ['kayu', 'kayu', 'Potongan Kayu Sisa Mebel (Chip)', ['Kadar Air' => '17', 'Jenis Kayu' => 'campur'], 30000, 380, 120, 'Dibuang ke lahan kosong', 'PUBLISHED'],
            ['tahu', 'tahu', 'Ampas Tahu Segar Harian', ['Kadar Air' => '76', 'Umur Sejak Produksi' => '6'], 45000, 650, 220, 'Dibuang ke sungai', 'PUBLISHED'],
            ['garmen', 'kain', 'Kain Perca Katun Putih', ['Komposisi Serat' => '100% katun', 'Kadar Air' => '4'], 15000, 2600, 500, 'Dibakar', 'PUBLISHED'],
            ['garmen', 'kain', 'Perca Polyester Campur Warna', ['Komposisi Serat' => 'polyester 65%, katun 35%'], 12000, 1800, 450, 'Landfill', 'PUBLISHED'],
            ['kemasan', 'kertas', 'Kardus Bekas Grade OCC', ['Kadar Air' => '9', 'Grade' => 'OCC #11'], 40000, 2300, 300, 'Dijual ke pengepul', 'PUBLISHED'],
            ['kemasan', 'kertas', 'Sisa Potongan Kertas Duplex', ['Kadar Air' => '7', 'Grade' => 'duplex'], 20000, 1900, 280, 'Landfill', 'DIARSIPKAN'],
            ['alumindo', 'logam', 'Skrap Aluminium Profil 6063', ['Kemurnian' => '97', 'Jenis Paduan' => '6063'], 14000, 21500, 400, 'Dijual ke pengepul', 'PUBLISHED'],
            ['alumindo', 'logam', 'Geram Aluminium Bubut', ['Kemurnian' => '91'], 8000, 14800, 350, 'Landfill', 'PUBLISHED'],
            ['sirkular', 'pet', 'Flakes PET Hijau Hasil Sortir', ['Kadar Air' => '1.2', 'Warna' => 'hijau'], 16000, 7400, 650, 'Dijual ke pengepul', 'PUBLISHED'],
            ['sirkular', 'kertas', 'Kertas HVS Bekas Kantor', ['Kadar Air' => '6', 'Grade' => 'HVS sortir'], 10000, 2800, 300, 'Dibakar', 'PUBLISHED'],
            ['seller', 'plastik', 'Jumbo Bag PP Bekas Utuh', ['Kadar Air' => '2', 'Warna' => 'putih', 'Bentuk' => 'lembaran'], 12000, 5200, 600, 'Landfill', 'PUBLISHED'],
        ];

        $photos = ['plastik' => 'plastik.jpg', 'pet' => 'plastik-2.jpg', 'kayu' => 'serbuk-kayu.jpg', 'tahu' => 'ampas-tahu.jpg', 'kain' => 'kain.jpg', 'kertas' => 'kardus.jpg'];
        $listings = [];

        $existing = Listing::where('companies_id_company', $this->companies['seller']->id_company)->get();
        foreach ($existing as $listing) {
            $listing->update(['volume_kg' => max(20000, (float) $listing->volume_kg)]);
            $listings[] = $listing;
        }

        foreach ($rows as [$seller, $cat, $judul, $spek, $volume, $harga, $buang, $metode, $status]) {
            $company = $this->companies[$seller];
            $listing = Listing::create([
                'judul' => $judul, 'spesifikasi' => $spek, 'volume_kg' => $volume, 'stok_kg' => $volume,
                'harga_per_kg' => $harga, 'biaya_buang_per_kg' => $buang, 'metode_buang_sebelumnya' => $metode,
                'lokasi' => trim((string) last(explode(',', (string) $company->alamat))), 'status' => $status,
                'companies_id_company' => $company->id_company,
                'categories_id_categories' => $this->categories[$cat]->id_categories,
            ]);

            $sampleDate = $this->now->copy()->subDays(mt_rand(5, 60))->toDateString();
            $attachments = [['SURAT_NON_B3', 'listings/documents', 'surat-non-b3.pdf', null]];
            if (isset($photos[$cat])) {
                $attachments[] = ['FOTO_SAMPEL', 'listings/photos', 'sampel.jpg', $photos[$cat]];
            }
            if ($this->categories[$cat]->wajib_sertifikat_lab) {
                $attachments[] = ['SERTIFIKAT_LAB', 'listings/certificates', 'sertifikat-lab.pdf', null];
            }
            foreach ($attachments as [$jenis, $folder, $file, $photo]) {
                $path = "{$folder}/demo-{$listing->id_listings}-{$file}";
                DB::table('listing_attachments')->insert([
                    'jenis' => $jenis,
                    'file_url' => $photo ? $this->copyPhoto($photo, $path) : $this->placeholder('public', $path, "{$jenis} {$judul}"),
                    'tanggal_pengambilan' => $sampleDate,
                    'listings_id_listings' => $listing->id_listings,
                    'status' => 'AKTIF',
                ]);
            }

            if ($status === 'PUBLISHED') {
                $listings[] = $listing;
            }
        }

        // Jadwal pasokan rutin untuk listing yang produksinya berulang.
        foreach (['Ampas Tahu Segar Harian' => ['MINGGUAN', 3000], 'Serbuk Gergaji Jati & Mahoni' => ['BULANAN', 8000], 'Kardus Bekas Grade OCC' => ['BULANAN', 6000]] as $judul => [$freq, $vol]) {
            $listing = Listing::where('judul', $judul)->first();
            DB::table('supply_schedules')->insert([
                'frekuensi' => $freq, 'volume_per_periode_kg' => $vol,
                'mulai' => $this->now->copy()->subMonths(3)->startOfMonth()->toDateString(),
                'selesai' => $this->now->copy()->addMonths(9)->endOfMonth()->toDateString(),
                'listings_id_listings' => $listing->id_listings, 'status' => 'AKTIF',
            ]);
        }

        return $listings;
    }

    private function seedDemandRequests(): void
    {
        // [pembeli, kategori, volume, harga maks, hari berlaku (negatif = lewat), status, frekuensi, spesifikasi]
        $rows = [
            ['biomassa', 'kayu', 20000, 520, 45, 'TERBUKA', 'BULANAN', ['Kadar Air' => '<20%']],
            ['biomassa', 'kain', 5000, 1500, 20, 'TERBUKA', 'SEKALI', ['Komposisi Serat' => 'bebas']],
            ['pakan', 'tahu', 12000, 700, 30, 'TERBUKA', 'MINGGUAN', ['Umur Sejak Produksi' => '<12 jam']],
            ['kertasdaur', 'kertas', 25000, 2500, 60, 'TERBUKA', 'BULANAN', ['Grade' => 'OCC']],
            ['bantal', 'kain', 8000, 2800, 25, 'TERBUKA', 'BULANAN', ['Komposisi Serat' => 'katun / polyester']],
            ['polimer', 'pet', 15000, 9200, 40, 'TERBUKA', 'BULANAN', ['Kadar Air' => '<1.5%', 'Warna' => 'bening']],
            ['polimer', 'plastik', 10000, 6500, -10, 'KEDALUWARSA', 'SEKALI', ['Kadar Air' => '<3%']],
            ['buyer', 'plastik', 6000, 6000, -40, 'TERPENUHI', 'SEKALI', ['Kadar Air' => '<5%']],
            ['sirkular', 'logam', 4000, 20000, 15, 'TERBUKA', 'SEKALI', ['Kemurnian' => '>95%']],
            ['kertasdaur', 'kertas', 6000, 2000, 90, 'DRAF', 'SEKALI', ['Grade' => 'HVS']],
        ];

        foreach ($rows as [$buyer, $cat, $vol, $harga, $days, $status, $freq, $spek]) {
            DB::table('demand_requests')->insert([
                'spesifikasi_min' => json_encode($spek), 'volume_kg' => $vol, 'harga_maks' => $harga,
                'berlaku_sampai' => $this->now->copy()->addDays($days)->toDateString(), 'status' => $status,
                'companies_id_company' => $this->companies[$buyer]->id_company,
                'categories_id_categories' => $this->categories[$cat]->id_categories, 'frekuensi' => $freq,
            ]);
        }
    }

    /**
     * RFQ yang belum (atau tidak akan) menjadi PO: negosiasi berjalan dan semua status akhir RFQ.
     *
     * @param  list<Listing>  $listings
     */
    private function seedOpenRfqs(array $listings): void
    {
        $plan = ['TERBUKA', 'TERBUKA', 'NEGOSIASI', 'NEGOSIASI', 'NEGOSIASI', 'DITOLAK', 'KEDALUWARSA', 'DITARIK'];

        foreach ($plan as $i => $status) {
            $listing = $listings[($i * 5 + 2) % count($listings)];
            $buyer = $this->pickBuyer($listing, $i);
            $rounds = match ($status) {
                'TERBUKA' => 1, 'NEGOSIASI' => mt_rand(2, 3), default => 2,
            };
            $volume = $this->roundTo(mt_rand(1000, 6000), 100);
            $tenggat = in_array($status, ['TERBUKA', 'NEGOSIASI'], true)
                ? $this->now->copy()->addDays(mt_rand(1, 6))
                : $this->now->copy()->subDays(mt_rand(3, 40));

            $rfq = RfqContract::create([
                'tenggat' => $tenggat, 'status' => $status,
                'listings_id_listings' => $listing->id_listings, 'companies_id_company' => $buyer->id_company,
            ]);
            $this->offers($rfq, $listing, $buyer, $volume, (float) $listing->harga_per_kg, $rounds);
        }
    }

    // ---------------------------------------------------------------- order

    /**
     * @param  list<Listing>  $listings
     */
    private function seedOrders(array $listings): void
    {
        // [status akhir, hari sejak langkah terakhir, opsi]
        $plan = [];
        foreach ([170, 155, 141, 126, 112, 98, 84, 71, 58, 45, 33, 21, 12, 6, 3] as $i => $ago) {
            $plan[] = ['SELESAI', $ago, ['via_sengketa' => $i === 9]];
        }
        $plan = array_merge($plan, [
            ['DRAFT', 0, []], ['DRAFT', 1, []],
            ['MENUNGGU_KONFIRMASI', 0, []], ['MENUNGGU_KONFIRMASI', 2, []], ['MENUNGGU_KONFIRMASI', 5, []],
            ['DIKONFIRMASI', 1, []], ['DIKONFIRMASI', 3, []], ['DIKONFIRMASI', 0, ['inspeksi' => true]], ['DIKONFIRMASI', 2, ['inspeksi' => true]],
            ['INSPEKSI', 0, []], ['INSPEKSI', 1, []], ['INSPEKSI', 4, []],
            ['LOLOS_INSPEKSI', 1, []], ['LOLOS_INSPEKSI', 3, []],
            ['GAGAL_INSPEKSI', 2, []],
            ['INVOICED', 1, ['bayar' => 'MENUNGGU']], ['INVOICED', 2, ['bayar' => 'MENUNGGU']], ['INVOICED', 4, ['bayar' => 'DITOLAK']], ['INVOICED', 9, []],
            ['DIBAYAR', 0, []], ['DIBAYAR', 2, []],
            ['DALAM_PENGIRIMAN', 0, []], ['DALAM_PENGIRIMAN', 1, []], ['DALAM_PENGIRIMAN', 1, []],
            ['DITERIMA', 0, []], ['DITERIMA', 2, []],
            ['SENGKETA', 3, ['jenis' => 'SELISIH_TONASE', 'sengketa' => 'DIBUKA']], ['SENGKETA', 8, ['jenis' => 'KADAR_AIR', 'sengketa' => 'DIPROSES']],
            ['DIBATALKAN', 11, []], ['DIBATALKAN', 26, []],
            ['DITOLAK', 15, []],
        ]);

        foreach ($plan as $i => [$status, $ago, $opt]) {
            $listing = $listings[($i * 7) % count($listings)];
            $this->createOrder($listing, $this->pickBuyer($listing, $i), $status, $ago, $opt);
        }
    }

    /**
     * @param  array<string, mixed>  $opt
     */
    private function createOrder(Listing $listing, Company $buyer, string $target, int $agoDays, array $opt): void
    {
        $seller = $listing->company;
        $category = $listing->category;
        $path = $this->pathTo($target, (bool) ($opt['via_sengketa'] ?? false));

        // Waktu tiap langkah dihitung mundur dari langkah terakhir.
        $times = [];
        $t = $this->now->copy()->subDays($agoDays)->subMinutes(mt_rand(30, 600));
        foreach (array_reverse($path) as $step) {
            $times[$step] = $t->copy();
            $t->subHours(mt_rand(5, 52));
        }
        $start = $times[$path[0]];

        $volume = $this->roundTo(mt_rand(800, (int) min(9000, (float) $listing->volume_kg / 3)), 50);
        $harga = $this->roundTo((float) $listing->harga_per_kg * mt_rand(90, 100) / 100, 50);
        $jarak = round($this->distanceKm($seller, $buyer) * 1.25, 2); // faktor jalan raya atas jarak garis lurus

        $rfq = RfqContract::create([
            'volume_kg_disepakati' => $volume, 'harga_disepakati' => $harga,
            'tenggat' => $start->copy()->subHours(2), 'status' => 'DISEPAKATI',
            'listings_id_listings' => $listing->id_listings, 'companies_id_company' => $buyer->id_company,
        ]);
        $this->offers($rfq, $listing, $buyer, $volume, $harga, mt_rand(1, 3), true);

        $po = PurchaseOrder::create([
            'volume_kg' => $volume, 'harga_per_kg' => $harga,
            'toleransi_persen' => $category->toleransi_susut_persen, 'kadar_air_maks_persen' => $category->kadar_air_maks_persen,
            'toleransi_kadar_air_poin' => $category->toleransi_kadar_air_poin, 'jarak_km' => $jarak,
            'status' => $target, 'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
            'companies_id_seller' => $seller->id_company, 'companies_id_buyer' => $buyer->id_company,
        ]);

        $sellerUser = $this->userOf($seller);
        $buyerUser = $this->userOf($buyer);
        $inspector = $this->pickInspector((int) $category->id_categories, $po->id_purchase_order);

        $prev = null;
        foreach ($path as $step) {
            $actor = match ($step) {
                'DRAFT', 'MENUNGGU_KONFIRMASI', 'DITERIMA', 'SENGKETA', 'DIBATALKAN' => $buyerUser,
                'DIKONFIRMASI', 'DITOLAK', 'INVOICED', 'DALAM_PENGIRIMAN' => $sellerUser,
                'INSPEKSI', 'LOLOS_INSPEKSI', 'GAGAL_INSPEKSI' => $inspector,
                default => $this->admin,
            };
            DB::table('order_status_history')->insert([
                'dari' => $prev, 'ke' => $step, 'alasan' => self::REASONS[$step], 'waktu' => $times[$step],
                'purchase_orders_id_purchase_order' => $po->id_purchase_order, 'users_id_user' => $actor->id_user,
            ]);
            $prev = $step;
        }

        $reached = fn (string $s) => in_array($s, $path, true);
        $ctx = compact('po', 'listing', 'category', 'seller', 'buyer', 'sellerUser', 'buyerUser', 'inspector', 'times', 'target', 'opt');

        if ($reached('INSPEKSI') || ! empty($opt['inspeksi'])) {
            $this->inspection($ctx);
        }
        if ($reached('INVOICED')) {
            $this->invoice($ctx);
        }
        $receipt = null;
        if ($reached('DALAM_PENGIRIMAN')) {
            $receipt = $this->shipment($ctx);
        }
        if ($reached('SENGKETA')) {
            $this->dispute($ctx);
        }
        if ($target === 'SELESAI' && $receipt) {
            $this->impact($ctx, $receipt);
            $this->reviews($ctx);
        }
        if ($target === 'MENUNGGU_KONFIRMASI') {
            $this->notify($sellerUser, 'PO_BARU', ['po' => $po->id_purchase_order, 'pesan' => 'PO-'.$po->id_purchase_order.' menunggu konfirmasi Anda'], $times[$target], false);
        }
    }

    /**
     * @return list<string>
     */
    private function pathTo(string $target, bool $viaSengketa): array
    {
        if (isset(self::EXIT_FROM[$target])) {
            $from = self::EXIT_FROM[$target];

            return [...array_slice(self::CHAIN, 0, array_search($from, self::CHAIN, true) + 1), $target];
        }
        $path = array_slice(self::CHAIN, 0, array_search($target, self::CHAIN, true) + 1);
        if ($viaSengketa && $target === 'SELESAI') {
            array_splice($path, -1, 0, ['SENGKETA']);
        }

        return $path;
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function inspection(array $ctx): void
    {
        /** @var PurchaseOrder $po */
        $po = $ctx['po'];
        $target = $ctx['target'];
        $times = $ctx['times'];
        $lapangan = $ctx['category']->tingkat_risiko === 'TINGGI' || (float) $po->volume_kg >= 5000;

        [$status, $jadwal] = match ($target) {
            'DIKONFIRMASI' => ['DITUGASKAN', $this->now->copy()->addDays(mt_rand(0, 5))->setTime(mt_rand(8, 14), [0, 30][mt_rand(0, 1)])],
            'INSPEKSI' => ['DIPROSES', $this->now->copy()->addDays(mt_rand(0, 2))->setTime(mt_rand(8, 15), 0)],
            default => ['SELESAI', $times['INSPEKSI']->copy()->addHours(1)],
        };

        $id = DB::table('inspections')->insertGetId([
            'mode_verifikasi' => $lapangan ? 'LAPANGAN' : 'DESK',
            'alasan_mode' => $lapangan ? 'Kategori risiko tinggi atau volume ≥ 5 ton: wajib cek lapangan' : 'Risiko rendah dan volume kecil: cukup verifikasi dokumen dan foto',
            'jadwal' => $jadwal, 'status' => $status,
            'purchase_orders_id_purchase_order' => $po->id_purchase_order, 'users_id_user' => $ctx['inspector']->id_user,
        ]);

        if ($status !== 'DITUGASKAN') {
            $gagal = $target === 'GAGAL_INSPEKSI';
            $maks = (float) ($po->kadar_air_maks_persen ?? 10);
            $airAktual = $gagal ? $maks + (float) ($po->toleransi_kadar_air_poin ?? 1) + 2.4 : round($maks * mt_rand(55, 95) / 100, 1);
            DB::table('inspection_results')->insert([
                'parameter_uji' => json_encode([
                    'kadar_air_persen' => $airAktual,
                    'kadar_air_maks_persen' => $maks,
                    'kontaminan' => $gagal ? 'tanah dan air tergenang' : 'tidak ditemukan',
                    'catatan' => $gagal ? 'Sampel basah, melewati batas toleransi kategori' : 'Sesuai spesifikasi listing',
                ]),
                'sumber_bukti' => $lapangan ? 'LAPANGAN' : 'FOTO',
                'hasil' => $status === 'DIPROSES' ? null : ($gagal ? 'GAGAL' : 'LULUS'),
                'lampiran_url' => $status === 'DIPROSES' ? null : $this->placeholder('local', "inspections/demo-{$id}-hasil.pdf", "Hasil inspeksi PO-{$po->id_purchase_order}"),
                'status' => $status === 'DIPROSES' ? 'DRAF' : 'FINAL',
                'inspections_id_inspection' => $id,
            ]);
        }

        if ($status !== 'DITUGASKAN') {
            $menunggu = $status === 'DIPROSES';
            DB::table('document_verifications')->insert([
                'verifiable_type' => PurchaseOrder::class, 'verifiable_id' => $po->id_purchase_order,
                'checklist' => json_encode(array_fill_keys(array_keys(DocumentVerification::CHECKLIST_ITEMS), ! $menunggu)),
                'status' => $menunggu ? 'MENUNGGU' : 'SETUJU',
                'purchase_orders_id_purchase_order' => $po->id_purchase_order, 'users_id_user' => $ctx['inspector']->id_user,
                'alasan' => null,
            ]);
        }

        $this->notify($ctx['inspector'], 'INSPEKSI_DITUGASKAN', ['po' => $po->id_purchase_order, 'inspeksi' => $id, 'pesan' => 'Anda ditugaskan memeriksa PO-'.$po->id_purchase_order], $times['DIKONFIRMASI'], $status === 'SELESAI');
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function invoice(array $ctx): void
    {
        /** @var PurchaseOrder $po */
        $po = $ctx['po'];
        $issued = $ctx['times']['INVOICED'];
        $paid = isset($ctx['times']['DIBAYAR']);
        $total = round((float) $po->volume_kg * (float) $po->harga_per_kg, 2);

        $invoiceId = DB::table('invoices')->insertGetId([
            'nomor' => 'INV-'.$issued->format('Ym').'-'.str_pad((string) $po->id_purchase_order, 4, '0', STR_PAD_LEFT),
            'total' => $total, 'jatuh_tempo' => $issued->copy()->addDays(7)->toDateString(),
            'status' => $paid ? 'DIBAYAR' : 'TERBIT', 'purchase_orders_id_purchase_order' => $po->id_purchase_order,
        ]);

        $bayar = $paid ? 'VALID' : ($ctx['opt']['bayar'] ?? null);
        if ($bayar === null) {
            return;
        }

        $rekening = 'BCA '.mt_rand(100, 999).'****'.mt_rand(10, 99).' a.n. '.$ctx['buyer']->nama;
        if ($bayar === 'DITOLAK') {
            DB::table('payments')->insert([
                'bukti_url' => $this->placeholder('local', "payments/demo-{$invoiceId}-1.pdf", 'Bukti transfer'),
                'tgl_transfer' => $issued->copy()->addDay()->toDateString(), 'nominal' => $total - 1500000,
                'rekening_asal' => $rekening, 'status_verifikasi' => 'DITOLAK',
                'alasan_tolak' => 'Nominal transfer kurang Rp1.500.000 dari total invoice', 'invoices_id_invoice' => $invoiceId,
            ]);

            return;
        }

        DB::table('payments')->insert([
            'bukti_url' => $this->placeholder('local', "payments/demo-{$invoiceId}.pdf", 'Bukti transfer'),
            'tgl_transfer' => ($paid ? $ctx['times']['DIBAYAR']->copy()->subHours(6) : $issued->copy()->addHours(20))->toDateString(),
            'nominal' => $total, 'rekening_asal' => $rekening, 'status_verifikasi' => $bayar,
            'alasan_tolak' => null, 'invoices_id_invoice' => $invoiceId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return array{diterima: float, dikirim: float}|null
     */
    private function shipment(array $ctx): ?array
    {
        /** @var PurchaseOrder $po */
        $po = $ctx['po'];
        $times = $ctx['times'];
        $arrived = isset($times['DITERIMA']);
        $dikirim = round((float) $po->volume_kg * mt_rand(985, 1000) / 1000, 2);
        $armada = [['Truk Engkel', 'B 9'], ['Truk Fuso', 'D 8'], ['Colt Diesel', 'L 9'], ['Truk Tronton', 'H 1'], ['Pickup L300', 'K 1']];
        [$jenis, $prefix] = $armada[mt_rand(0, count($armada) - 1)];
        $sopir = ['Slamet Riyadi', 'Joko Susilo', 'Ujang Hermawan', 'Rahmat Hidayat', 'Komarudin', 'Sugeng Widodo'];
        $kota = fn (Company $c) => trim((string) last(explode(',', (string) $c->alamat)));

        $this->manifestSeq++;
        $id = DB::table('shipments_manifests')->insertGetId([
            'armada' => $jenis, 'plat' => $prefix.mt_rand(100, 999).' '.chr(mt_rand(65, 90)).chr(mt_rand(65, 90)),
            'sopir' => $sopir[mt_rand(0, count($sopir) - 1)], 'manifes_url' => null,
            'status' => $arrived ? 'TIBA' : 'DALAM_PERJALANAN',
            'purchase_orders_id_purchase_order' => $po->id_purchase_order, 'berat_dikirim_kg' => $dikirim,
            'jadwal' => $times['DALAM_PENGIRIMAN'], 'rute' => $kota($ctx['seller']).' → '.$kota($ctx['buyer']),
            'no_manifes' => 'MNF-'.$times['DALAM_PENGIRIMAN']->format('ymd').'-'.str_pad((string) $this->manifestSeq, 3, '0', STR_PAD_LEFT),
        ]);
        if (Route::has('shipments.manifest')) {
            DB::table('shipments_manifests')->where('id_shipment_manifest', $id)->update(['manifes_url' => route('shipments.manifest', $id, false)]);
        }
        DB::table('shipment_documents')->insert([
            'jenis' => 'SURAT_JALAN',
            'file_url' => $this->placeholder('local', "shipments/demo-{$id}-surat-jalan.pdf", "Surat jalan PO-{$po->id_purchase_order}"),
            'shipments_manifests_id_shipment_manifest' => $id,
        ]);

        if (! $arrived) {
            return null;
        }

        // Sengketa selisih tonase: susut sengaja di luar toleransi. Lainnya: susut wajar di dalam toleransi.
        $tol = (float) $po->toleransi_persen;
        $outside = ($ctx['opt']['jenis'] ?? null) === 'SELISIH_TONASE' || ! empty($ctx['opt']['via_sengketa']);
        $susut = $outside ? $tol + mt_rand(15, 40) / 10 : $tol * mt_rand(10, 80) / 100;
        $diterima = round((float) $po->volume_kg * (1 - $susut / 100), 2);
        $selisih = round(((float) $po->volume_kg - $diterima) / (float) $po->volume_kg * 100, 2);
        $penyesuaian = $outside ? round(((float) $po->volume_kg * (1 - $tol / 100) - $diterima) * (float) $po->harga_per_kg, 2) : null;
        $maks = (float) ($po->kadar_air_maks_persen ?? 10);
        $airKey = ($ctx['opt']['jenis'] ?? null) === 'KADAR_AIR';

        DB::table('goods_receipts')->insert([
            'berat_diterima_kg' => $diterima, 'berat_ditolak_kg' => $airKey ? round($diterima * 0.08, 2) : 0,
            'kadar_air_persen' => $airKey ? $maks + (float) ($po->toleransi_kadar_air_poin ?? 1) + 1.5 : round($maks * mt_rand(50, 95) / 100, 2),
            'selisih_persen' => $selisih, 'status_toleransi' => $outside ? 'DI_LUAR' : 'DALAM',
            'nilai_penyesuaian_rp' => $penyesuaian,
            'kondisi' => $airKey ? 'Sebagian karung basah' : 'Baik, kemasan utuh',
            'catatan' => $outside ? 'Selisih timbang melebihi toleransi kategori' : null,
            'waktu' => $times['DITERIMA'], 'shipments_manifests_id_shipment_manifest' => $id,
            'status_penyesuaian' => $outside ? ($ctx['target'] === 'SELESAI' ? 'SELESAI' : 'MENUNGGU') : 'TIDAK_ADA',
        ]);

        return ['diterima' => $diterima, 'dikirim' => $dikirim];
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function dispute(array $ctx): void
    {
        $selesai = $ctx['target'] === 'SELESAI';
        $jenis = $ctx['opt']['jenis'] ?? 'SELISIH_TONASE';
        $po = $ctx['po'];

        DB::table('disputes')->insert([
            'jenis' => $jenis,
            'deskripsi' => $jenis === 'KADAR_AIR'
                ? 'Kadar air saat diterima melebihi batas di PO; sebagian karung basah dan ditolak gudang.'
                : 'Berat timbang di gudang Buyer lebih rendah dari manifes di luar toleransi susut kategori.',
            'bukti_url' => $this->placeholder('local', "disputes/demo-po-{$po->id_purchase_order}.pdf", 'Bukti sengketa'),
            'status' => $selesai ? 'SELESAI' : ($ctx['opt']['sengketa'] ?? 'DIBUKA'),
            'putusan' => $selesai ? 'Seller mengembalikan nilai selisih di luar toleransi; order ditutup.' : null,
            'purchase_orders_id_purchase_order' => $po->id_purchase_order,
            'companies_id_company' => $ctx['buyer']->id_company,
        ]);
        $this->notify($this->admin, 'SENGKETA_BARU', ['po' => $po->id_purchase_order, 'pesan' => 'Sengketa baru pada PO-'.$po->id_purchase_order], $ctx['times']['SENGKETA'], $selesai);
    }

    /**
     * Impact Calculator (PRD §4.5) dengan faktor versi aktif kategori.
     *
     * @param  array<string, mixed>  $ctx
     * @param  array{diterima: float, dikirim: float}  $receipt
     */
    private function impact(array $ctx, array $receipt): void
    {
        /** @var PurchaseOrder $po */
        $po = $ctx['po'];
        $factor = DB::table('impact_factors')->where('categories_id_categories', $ctx['category']->id_categories)->where('aktif', 1)->orderByDesc('versi')->first();
        if (! $factor) {
            return;
        }

        $kg = $receipt['diterima'];
        $avoided = $kg * (float) $factor->substitution_rate * ((float) $factor->ef_buang - (float) $factor->ef_proses);
        $transport = ($kg / 1000) * (float) $po->jarak_km * (float) $factor->ef_trans;

        DB::table('impact_logs')->insert([
            'tonase_ton' => round($kg / 1000, 3),
            'hemat_seller_rp' => round($receipt['dikirim'] * (float) ($ctx['listing']->biaya_buang_per_kg ?? $factor->biaya_buang_ref), 2),
            'hemat_buyer_rp' => round(max(0, $kg * ((float) $factor->harga_virgin_ref - (float) $po->harga_per_kg)), 2),
            'co2e_dihindari_kg' => round($avoided, 2),
            'co2e_angkutan_kg' => round($transport, 2),
            'co2e_net_kg' => round($avoided - $transport, 2),
            'versi_faktor' => $factor->versi,
            'purchase_orders_id_purchase_order' => $po->id_purchase_order,
        ]);
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function reviews(array $ctx): void
    {
        $komentar = [
            5 => ['Material sesuai spesifikasi, pengiriman tepat waktu.', 'Komunikasi cepat dan dokumen lengkap.', 'Mitra yang bisa diandalkan untuk pasokan rutin.'],
            4 => ['Secara umum baik, jadwal kirim mundur satu hari.', 'Mutu sesuai, kemasan bisa lebih rapi.'],
            3 => ['Ada selisih timbang, tapi diselesaikan dengan baik.'],
        ];
        $po = $ctx['po'];
        $sengketa = ! empty($ctx['opt']['via_sengketa']);

        foreach ([[$ctx['buyer'], $ctx['seller']], [$ctx['seller'], $ctx['buyer']]] as [$from, $to]) {
            $rating = $sengketa ? 3 : [5, 5, 4][mt_rand(0, 2)];
            DB::table('reviews')->insert([
                'rating' => $rating, 'komentar' => $komentar[$rating][mt_rand(0, count($komentar[$rating]) - 1)],
                'purchase_orders_id_purchase_order' => $po->id_purchase_order,
                'companies_id_company' => $from->id_company, 'companies_id_target_company' => $to->id_company,
            ]);
        }
    }

    // ---------------------------------------------------------------- tata kelola

    private function seedGovernance(): void
    {
        $c = $this->companies;

        foreach ([
            ['TEMUAN', 'tahu', 'Penyimpanan ampas tahu terbuka; risiko bau dan lindi. Wajib wadah tertutup.', 14, 'TEMUAN'],
            ['PERINGATAN', 'kayu', 'Debu serbuk kayu di area muat melebihi batas; pasang penyekat.', 7, 'DITINDAKLANJUTI'],
            ['TEMUAN', 'plastindo', 'Label surat non-B3 tidak menyebut nomor lot.', -20, 'SELESAI'],
        ] as [$jenis, $key, $desk, $days, $status]) {
            DB::table('compliance_notes')->insert([
                'jenis' => $jenis, 'deskripsi' => $desk, 'tenggat' => $this->now->copy()->addDays($days)->toDateString(),
                'status_tindak_lanjut' => $status, 'companies_id_company' => $c[$key]->id_company, 'users_id_user' => $this->admin->id_user,
            ]);
        }

        foreach ([
            ['biomassa', ['kategori' => [$this->categories['kayu']->id_categories], 'lokasi' => 'Jawa Tengah', 'harga_maks' => 500]],
            ['polimer', ['kategori' => [$this->categories['pet']->id_categories, $this->categories['plastik']->id_categories], 'volume_min_kg' => 3000]],
            ['buyer', ['kategori' => [$this->categories['plastik']->id_categories], 'lokasi' => 'Bekasi']],
        ] as [$key, $kriteria]) {
            DB::table('watchlists')->insert(['kriteria' => json_encode($kriteria), 'aktif' => 1, 'users_id_user' => $this->users[$key]->id_user]);
        }

        foreach (['plastindo', 'kayu', 'tahu', 'ditolak'] as $i => $key) {
            $ditolak = $key === 'ditolak';
            DB::table('audit_logs')->insert([
                'waktu' => $this->now->copy()->subDays(160 - $i * 10), 'users_id_user' => $this->admin->id_user,
                'aksi' => $ditolak ? 'company.verification.rejected' : 'company.verification.approved',
                'auditable_type' => Company::class, 'auditable_id' => $c[$key]->id_company,
                'nilai_lama' => json_encode(['status_verifikasi' => 'PENDING']),
                'nilai_baru' => json_encode(['status_verifikasi' => $ditolak ? 'DITOLAK' : 'TERVERIFIKASI']),
                'ip_address' => '10.0.0.'.mt_rand(10, 60),
            ]);
        }

        $this->notify($this->users['buyer'], 'LISTING_COCOK', ['pesan' => 'Listing baru cocok dengan watchlist Anda: Jumbo Bag PP Bekas Utuh'], $this->now->copy()->subHours(5), false);
        $this->notify($this->users['seller'], 'ULASAN_BARU', ['pesan' => 'Anda menerima ulasan bintang 5 dari pembeli'], $this->now->copy()->subDays(2), true);
    }

    /**
     * Stok listing = volume dikurangi PO yang sudah dikonfirmasi dan belum batal (reservasi stok PBI PO).
     */
    private function syncStock(): void
    {
        $reserved = DB::table('purchase_orders AS po')
            ->join('rfq_contracts AS r', 'r.id_rfq_contract', '=', 'po.rfq_contracts_id_rfq_contract')
            ->whereNotIn('po.status', ['DRAFT', 'MENUNGGU_KONFIRMASI', 'DITOLAK', 'DIBATALKAN'])
            ->groupBy('r.listings_id_listings')
            ->selectRaw('r.listings_id_listings AS id, SUM(po.volume_kg) AS kg')
            ->pluck('kg', 'id');

        foreach (Listing::all() as $listing) {
            $listing->update(['stok_kg' => max(0, (float) $listing->volume_kg - (float) ($reserved[$listing->id_listings] ?? 0))]);
        }
    }

    // ---------------------------------------------------------------- pembantu

    private function offers(RfqContract $rfq, Listing $listing, Company $buyer, float $volume, float $finalPrice, int $rounds, bool $agreed = false): void
    {
        $start = $this->roundTo($finalPrice * 0.88, 50);
        for ($n = 1; $n <= $rounds; $n++) {
            $fromBuyer = $n % 2 === 1;
            $price = $agreed && $n === $rounds ? $finalPrice : $this->roundTo($start + ($finalPrice - $start) * $n / max(1, $rounds), 50);
            DB::table('rfq_offers')->insert([
                'volume_kg' => $volume, 'harga_per_kg' => $price,
                'catatan' => $agreed && $n === $rounds ? 'Sepakat sesuai spesifikasi listing.' : ($fromBuyer ? 'Penawaran untuk kebutuhan produksi bulan ini.' : 'Mutu terjaga, kami ajukan penyesuaian harga.'),
                'urutan' => $n, 'rfq_contracts_id_rfq_contract' => $rfq->id_rfq_contract,
                'companies_id_company' => $fromBuyer ? $buyer->id_company : $listing->companies_id_company,
            ]);
        }
    }

    private function pickBuyer(Listing $listing, int $i): Company
    {
        $byCategory = [
            'Serbuk Kayu Gergaji' => ['biomassa', 'sirkular'],
            'Ampas Tahu Basah' => ['pakan'],
            'Kertas & Kardus Bekas' => ['kertasdaur', 'sirkular'],
            'Kain Perca Tekstil' => ['bantal', 'biomassa'],
            'Logam Aluminium Scrap' => ['sirkular', 'buyer'],
        ];
        $pool = $byCategory[$listing->category->nama] ?? ['polimer', 'buyer', 'sirkular'];
        $pool = array_values(array_filter($pool, fn ($k) => $this->companies[$k]->id_company !== $listing->companies_id_company));

        return $this->companies[$pool[$i % count($pool)]];
    }

    private function pickInspector(int $categoryId, int $seed): User
    {
        $match = array_values(array_filter($this->inspectors, fn (User $u) => in_array($categoryId, $u->inspectorProfile->expertiseIds(), true)));
        $pool = $match ?: $this->inspectors;

        return $pool[$seed % count($pool)];
    }

    private function userOf(Company $company): User
    {
        return User::where('id_company', $company->id_company)->orderBy('id_user')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function notify(User $user, string $jenis, array $payload, Carbon $at, bool $read): void
    {
        DB::table('notifications')->insert([
            'jenis' => $jenis, 'payload' => json_encode($payload), 'created_at' => $at,
            'read_at' => $read ? $at->copy()->addHours(3) : null, 'users_id_user' => $user->id_user,
        ]);
    }

    private function distanceKm(Company $a, Company $b): float
    {
        // Company belum punya @property lat/lng, jadi koordinat dibaca lewat getAttribute().
        [$latA, $lngA] = [$a->getAttribute('lat'), $a->getAttribute('lng')];
        [$latB, $lngB] = [$b->getAttribute('lat'), $b->getAttribute('lng')];
        if ($latA === null || $latB === null) {
            return 40.0;
        }
        $rad = M_PI / 180;
        $dLat = ($latB - $latA) * $rad;
        $dLng = ($lngB - $lngA) * $rad;
        $h = sin($dLat / 2) ** 2 + cos($latA * $rad) * cos($latB * $rad) * sin($dLng / 2) ** 2;

        return max(5.0, 6371 * 2 * asin(min(1, sqrt($h))));
    }

    private function roundTo(float $value, int $step): float
    {
        return (float) (round($value / $step) * $step);
    }

    /** Berkas PDF kecil agar tautan unduh di demo tidak 404. */
    private function placeholder(string $disk, string $path, string $label): string
    {
        $text = 'DATA DEMO SymbioNusa - '.preg_replace('/[^A-Za-z0-9 .,\-\/]/', '', $label);
        $stream = "BT /F1 14 Tf 60 760 Td ({$text}) Tj ET";
        $pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            ."3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n"
            .'4 0 obj<</Length '.strlen($stream).">>stream\n{$stream}\nendstream endobj\n"
            ."5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";
        Storage::disk($disk)->put($path, $pdf);

        return $path;
    }

    private function copyPhoto(string $file, string $path): string
    {
        $source = public_path('images/material/'.$file);
        if (is_file($source)) {
            Storage::disk('public')->put($path, (string) file_get_contents($source));

            return $path;
        }

        return $this->placeholder('public', str_replace('.jpg', '.pdf', $path), 'Foto sampel');
    }
}
