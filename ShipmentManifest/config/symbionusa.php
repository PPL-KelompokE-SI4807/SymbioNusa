<?php

/*
 | Parameter kebijakan platform SymbioNusa (proposal bagian 3.1.1-D).
 | Saat integrasi: gabungkan key di bawah ini ke config/symbionusa.php milik tim.
 */
return [

    // true  = mode mandiri PBI-11 (tabel stub + pemilih peran demo, tanpa login).
    // false = mode terintegrasi (pakai tabel/model/auth PBI lain).
    'standalone' => (bool) env('PBI11_STANDALONE', true),

    // Awalan nomor manifes: MNF-YYYYMM-0001
    'manifest_prefix' => 'MNF',

    // Disk penyimpanan surat jalan (privat; diunduh lewat controller).
    'storage_disk' => env('SYMBIONUSA_DISK', 'local'),

    // Ukuran maksimal berkas unggahan (KB) - proposal: 5 MB (PDF, JPG, PNG).
    'upload_max_kb' => 5120,

    // Jumlah maksimal surat jalan per pengiriman.
    'shipment_max_documents' => 5,
];
