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

use App\Nova\Banner; // ✅ Ensure it's referencing Nova resource, NOT provider!
use App\Nova\City;
use App\Nova\Car;
use App\Nova\Gallery;
use App\Nova\Faq;
use App\Nova\Facility;
use App\Nova\Payment;
use App\Nova\Coupon;
use App\Nova\Page;
use App\Nova\Dashboards\Main;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot(); // Ensure Nova is booted first
        //$this->authorizeNova();
        Nova::dashboards([
            new Main(), // ✅ Ensure this exists
        ]);
        Nova::mainMenu(function ($request) {
            return [
                //MenuSection::dashboard('Dashboard')->icon('chart-bar'),

                MenuSection::make('City Management', [
                    Banner::make(),
                    City::make(),
                ])->icon('building')->collapsable(),

                MenuSection::make('Car Management', [
                    Car::make(),
                    Gallery::make(),
                    Facility::make(),
                ])->icon('car')->collapsable(),

                MenuSection::make('Support', [
                    Faq::make(),
                    Page::make(),
                ])->icon('help-circle')->collapsable(),

                MenuSection::make('Payments & Discounts', [
                    Payment::make(),
                    Coupon::make(),
                ])->icon('credit-card')->collapsable(),
            ];
        });
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
