<?php

namespace App\Console\Commands;

use App\Services\Company\CompanyVerificationService;
use Illuminate\Console\Command;

/**
 * PBI-08 (SYM-12): perusahaan yang dokumen wajibnya kedaluwarsa kembali ke Tinjauan Ulang.
 */
class ReviewExpiredCompanyDocumentsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'perusahaan:tinjau-ulang-kedaluwarsa';

    /**
     * @var string
     */
    protected $description = 'Menandai dokumen perusahaan yang kedaluwarsa dan mengembalikan perusahaan terverifikasi ke Tinjauan Ulang bila dokumen wajibnya kedaluwarsa';

    public function handle(CompanyVerificationService $service): int
    {
        $count = $service->reviewCompaniesWithExpiredDocuments();
        $this->info("{$count} perusahaan dikembalikan ke Tinjauan Ulang.");

        return self::SUCCESS;
    }
}
