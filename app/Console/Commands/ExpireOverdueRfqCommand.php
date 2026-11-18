<?php

namespace App\Console\Commands;

use App\Services\Rfq\RfqService;
use Illuminate\Console\Command;

class ExpireOverdueRfqCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rfq:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbarui status RFQ yang telah melewati tenggat menjadi KEDALUWARSA';

    /**
     * Execute the console command.
     */
    public function handle(RfqService $service): int
    {
        $count = $service->expireOverdue();
        $this->info("Berhasil memperbarui {$count} RFQ kedaluwarsa.");

        return Command::SUCCESS;
    }
}
