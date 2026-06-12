<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Lead;
use App\Observers\LeadObserver;

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
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });

        Lead::observe(LeadObserver::class);
    }
}
