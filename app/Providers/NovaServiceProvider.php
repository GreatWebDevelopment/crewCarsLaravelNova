<?php

namespace App\Providers;

use App\Nova\CarBrand;
use App\Nova\CarType;
use Illuminate\Support\Facades\Gate;
//use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\Menu;


class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot(); // Ensure Nova is booted first
    }
    /**
     * Register the Nova gate.
     */
    protected function gate(): void
    {
        if (app()->bound('gate')) { // Ensure Gate is registered before using it
            Gate::define('viewNova', function ($user) {
                return $user->role === 'admin'; // Adjust based on your user roles
            });
        }
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
    */
    protected function dashboards(): array
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();
    }
}
