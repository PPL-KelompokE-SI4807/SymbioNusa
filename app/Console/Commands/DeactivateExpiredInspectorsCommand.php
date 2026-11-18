<?php

namespace App\Console\Commands;

use App\Services\Inspector\InspectorService;
use Illuminate\Console\Command;

/**
 * PBI-01 (SYM-5): Inspector otomatis nonaktif ketika sertifikat kedaluwarsa (TEK-07).
 * Dijadwalkan harian di routes/console.php.
 */
class DeactivateExpiredInspectorsCommand extends Command
{
    protected $signature = 'inspector:nonaktifkan-kedaluwarsa';

    protected $description = 'Nonaktifkan Inspector yang masa berlaku sertifikatnya sudah lewat';

    public function handle(InspectorService $service): int
    {
        $count = $service->deactivateExpired();

        $this->info("{$count} Inspector dinonaktifkan karena sertifikat kedaluwarsa.");

        return self::SUCCESS;
    }
}
