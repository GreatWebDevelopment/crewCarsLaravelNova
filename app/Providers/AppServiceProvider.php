<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

use App\Services\ApiGatewayService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('apiKey', function () {
            return DB::table('settings')->first()->apiKey;
        });

        $this->app->singleton('set', function () {
            return DB::table('settings')->first();
        });

        $this->app->singleton(ApiGatewayService::class, function () {
            return new ApiGatewayService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::singleton('set', function() {
            return DB::table('settings')->first();
        });
    }
}
