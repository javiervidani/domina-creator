<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Tenancy\Tenancy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Runs while the creator's schema is entered: only pages behind the tenant middleware use this layout.
        \Illuminate\Support\Facades\View::composer('layouts.app', fn ($view) => $view->with('nav', app(\App\Library\NavStatus::class)->data()));
        $this->loadMigrationsFrom(database_path('migrations/central'));
    }
}
