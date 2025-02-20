<?php

namespace App\Providers;

use App\Nova\CarBrand;
use App\Nova\CarType;
use Illuminate\Support\Facades\Log;

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
use App\Nova\Booking;
use App\Nova\User;
//use App\Nova\Dashboards\Main;
use Illuminate\Support\Facades\Route;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot(); // Ensure Nova is booted first
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
        Nova::mainMenu(function ($request) {
            return [
                MenuSection::make('Car Management', [
                    MenuItem::resource(Car::class),
                    MenuItem::resource(Booking::class),
                ])->icon('car')->collapsable(),
                /*MenuSection::make('Bookings', [
                    Booking::make(),
                ])->icon('calendar')->collapsable(),*/
            ];
        });

        // ✅ Force Nova Home to Redirect to a Resource Instead of the Dashboard
        /*Nova::serving(function () {
                     Nova::mainMenu(function ($request) {
                          Log::info('✅ Nova mainMenu() is runng...');
          /*
                          return [
                              MenuSection::make('Banner', [Banner::make()])->icon('image'),
                              MenuSection::make('City', [City::make()])->icon('building'),
                              MenuSection::make('Car Management', [
                                  CarType::make(),
                                  CarBrand::make(),
                                  Car::make(),
                                  Gallery::make(),
                              ])->icon('car')->collapsable(),
                              MenuSection::make('Support', [
                                  Faq::make(),
                                  Facility::make(),
                              ])->icon('help-circle')->collapsable(),
                              MenuSection::make('Payments', [
                                  Payment::make(),
                                  Coupon::make(),
                              ])->icon('credit-card')->collapsable(),
                              MenuSection::make('Pages', [
                                  Page::make(),
                              ])->icon('file')->collapsable(),
                              MenuSection::make('Bookings', [
                                  Booking::make(),
                              ])->icon('calendar')->collapsable(),
                              MenuSection::make('Users', [
                                  \App\Nova\User::make(),
                              ])->icon('users')->collapsable(),
                          ];
                      });

       });
       Log::info('🔹 NovaServiceProvider: Booting...');

       Route::middleware(['web', 'nova'])->get('/nova', function () {
           return redirect('/nova/resources/cars'); // Change 'cars' to your preferred resource
       })->name('nova.pages.home');
           */
    }


    /**
     * Register the Nova gate.
     */
    protected function gate(): void
    {
        if (app()->bound('gate')) {
            Gate::define('viewNova', function ($user) {
                return isset($user->role) && $user->role === 'admin'; // ✅ Null check added
            });
        }
    }




    /**
     * Get the dashboards that should be listed in the Nova sidebar.
    */
    protected function dashboards(): array
    {
        return []; // ✅ Ensures Nova does NOT load the Main dashboard
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Log::info('🔹 NovaServiceProvider: Registering services...');
        parent::register();    }
}
