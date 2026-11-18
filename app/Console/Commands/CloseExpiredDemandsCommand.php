<?php

namespace App\Console\Commands;

use App\Services\Demand\DemandService;
use Illuminate\Console\Command;

class CloseExpiredDemandsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demand:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menutup otomatis permintaan kebutuhan (demand requests) yang telah melewati batas berlaku menjadi KEDALUWARSA';

    /**
     * Execute the console command.
     */
    public function handle(DemandService $service): int
    {
        $count = $service->closeExpiredDemands();
        $this->info("Berhasil menutup {$count} kebutuhan kedaluwarsa.");

        return Command::SUCCESS;
    }
}
