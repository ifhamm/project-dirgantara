<?php

namespace App\Providers;

use App\Models\MwsPart;
use App\Models\User;
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

        Gate::define('is-superadmin', function (User $user) {
            return $user->role === 'superadmin';
        });
        Gate::define('is-management', function (User $user) {
            return in_array($user->role, ['superadmin', 'admin']);
        });

        Gate::define('quality-inspector', function (User $user) {
            return $user->role === 'quality1';
        });

        Gate::define('quality-cdvr', function (User $user) {
            return $user->role === 'quality2';
        });

        Gate::define('mechanic', function (User $user) {
            return $user->role === 'mechanic';
        });

        Gate::define('customer', function (User $user) {
            return $user->role === 'customer';
        });

    }
}
