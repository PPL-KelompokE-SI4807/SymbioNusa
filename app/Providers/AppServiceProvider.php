<?php

namespace App\Providers;

use App\Models\DemandRequest;
use App\Models\RfqContract;
use App\Policies\DemandPolicy;
use App\Policies\RfqPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(RfqContract::class, RfqPolicy::class);
        Gate::policy(DemandRequest::class, DemandPolicy::class);
    }
}
