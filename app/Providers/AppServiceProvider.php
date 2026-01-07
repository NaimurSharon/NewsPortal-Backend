<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
        // Define gate for accessing Scalar documentation
        Gate::define('viewScalar', function (?User $user) {
            // Adjust the condition as needed; here we allow all authenticated users
            return $user !== null;
        });
    }
}
