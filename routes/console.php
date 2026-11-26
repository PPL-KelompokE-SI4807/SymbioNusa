<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PBI-01 (SYM-5): Inspector dengan sertifikat kedaluwarsa otomatis nonaktif.
Schedule::command('inspector:nonaktifkan-kedaluwarsa')->dailyAt('00:05')->timezone('Asia/Jakarta');

// PBI-08 (SYM-12): perusahaan dengan dokumen wajib kedaluwarsa kembali ke Tinjauan Ulang.
Schedule::command('perusahaan:tinjau-ulang-kedaluwarsa')->dailyAt('00:10')->timezone('Asia/Jakarta');
