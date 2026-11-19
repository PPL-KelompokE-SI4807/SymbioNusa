<?php

namespace App\Providers;

use App\Contracts\OrderTransition;
use App\Services\SimpleOrderTransition;
use Illuminate\Support\ServiceProvider;

class Pbi11ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // TITIK INTEGRASI: ganti dengan adapter ke OrderStateMachine (PBI-05)
        // $this->app->bind(OrderTransition::class, \App\Services\StateMachineOrderTransition::class);
        $this->app->bind(OrderTransition::class, SimpleOrderTransition::class);
    }

    public function boot(): void
    {
        // Tabel stub (companies, purchase_orders, dst.) hanya dimuat pada mode mandiri.
        if (config('symbionusa.standalone')) {
            $this->loadMigrationsFrom(database_path('migrations_standalone'));
        }
    }
}
