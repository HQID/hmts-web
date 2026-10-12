<?php

namespace App\Providers;

use App\Models\User;
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
        Gate::define('manage-command-center', fn (User $user): bool => $user->isAdmin());
        Gate::define('manage-periods-and-admins', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('void-cash-transaction', fn (User $user): bool => $user->isSuperAdmin());
    }
}
