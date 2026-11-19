<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Mode mandiri: isi data contoh PBI-11. Mode terintegrasi: seeder tim yang berlaku.
        if (config('symbionusa.standalone')) {
            $this->call(Pbi11StandaloneSeeder::class);
        }
    }
}
