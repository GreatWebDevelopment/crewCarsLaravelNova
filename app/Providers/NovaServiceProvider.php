<?php

namespace App\Providers;

use App\Nova\CarBrand;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Gate;

//use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\Menu;

use App\Nova\Banner; // ✅ Ensure it's referencing Nova resource, NOT provider!
use App\Nova\Car;
use App\Nova\Gallery;
use App\Nova\Faq;
use App\Nova\Facility;
use App\Nova\Payment;
use App\Nova\Coupon;
use App\Nova\Page;
use App\Nova\Booking;
use App\Nova\User;
use App\Nova\DriverLicense;
use App\Nova\Dashboards\Main;
use Illuminate\Support\Facades\Route;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot(); // Ensure Nova is booted first
        Nova::style('nova-custom', public_path('css/nova-custom.css'));

        Nova::serving(function () {
            // Register the Google Maps JavaScript API
            Nova::footer(function () {
                return '<script async defer src="https://maps.googleapis.com/maps/api/js?key='.env('GOOGLE_MAPS_API_KEY').'&libraries=places&callback=initAutocomplete"></script>';
            });
            // Register your custom script for Google Places autocomplete
            Nova::script('nova-google-places', asset('js/nova-google-places.js'));
            Nova::script('nova-google-maps', asset('js/nova-google-maps.js'));

        });
       // ✅ Restrict Nova Access to Admins
        Nova::auth(function ($request) {
            return auth()->user() && auth()->user()->hasRole('admin');
        });
        Nova::routes()
            ->withAuthenticationRoutes(default: true)
            ->withPasswordResetRoutes()
            ->register();
        Nova::mainMenu(function ($request) {
            return [
                MenuSection::make('Car Management', [
                    MenuItem::resource(Car::class),
                    MenuItem::resource(Booking::class),
                    MenuItem::resource(CarBrand::class),
                ])->icon('car')->collapsable(),
                // 🔹 User & Booking Management
                MenuSection::make('User & Booking Management', [
                    MenuItem::resource(User::class),
                    MenuItem::resource(Booking::class),
                ])->icon('users')->collapsable(),
                // 🔹 Documents
                MenuSection::make('Documents', [
                   MenuItem::resource(DriverLicense::class),
                ])->icon('identification')->collapsable(),
                // 🔹 Financials
                MenuSection::make('Financials', [
                    MenuItem::resource(Payment::class),
                    MenuItem::resource(Coupon::class),
                ])->icon('credit-card')->collapsable(),
                // 🔹 Support & Pages
                MenuSection::make('Support & Pages', [
                    MenuItem::resource(Faq::class),
                    MenuItem::resource(Page::class),
                    MenuItem::resource(Banner::class),
                ])->icon('life-buoy')->collapsable(),
            ];
        });


       Route::middleware(['web', 'nova'])->get('/nova', function () {
           return redirect('/nova/resources/cars'); // Change 'cars' to your preferred resource
       })->name('nova.pages.home');

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
        return [
            new Main(),
        ];
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();    }
}
