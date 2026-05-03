<?php

namespace App\Providers;

use App\Models\MwsPart;
use App\Policies\MwsPartPolicy;
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
        Gate::policy(MwsPart::class, MwsPartPolicy::class);
    }
}
