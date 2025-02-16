<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Menu\MenuItem;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function boot()
    {
        parent::boot(); // Ensure Nova is booted first
        Nova::mainMenu(function (Request $request) {
            return [
                MenuSection::dashboard(\App\Nova\Dashboards\Main::class)->icon('chart-bar'),

                MenuSection::make('Management', [
                    MenuItem::resource(\App\Nova\Book::class),
                    //MenuItem::resource(\App\Nova\Payment::class),
                    MenuItem::resource(\App\Nova\PaymentMethod::class),
                ])->icon('credit-card')->collapsable(),

                MenuSection::make('Cars', [
                    //MenuItem::resource(\App\Nova\CarBrands::class),
                   // MenuItem::resource(\App\Nova\CarTypes::class),
                    //MenuItem::resource(\App\Nova\VehicleMake::class),
                ])->icon('car')->collapsable(),
            ];
        });
    }

    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return $user->isAdmin(); // Adjust this based on your user roles
        });
    }

    protected function dashboards()
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    public function register()
    {
        parent::register();
    }
}
