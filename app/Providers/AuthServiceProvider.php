<?php

namespace App\Providers;
use Laravel\Nova\Nova;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('viewNova', function ($user) {
            return $user->is_admin; // Only allow admins
        });

        Nova::auth(function ($request) {
            return Gate::allows('viewNova', $request->user());
        });
    }
}