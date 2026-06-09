<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Controllers\TenantAssetsController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

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
        TenantAssetsController::$tenancyMiddleware = InitializeTenancyBySubdomain::class;

        InitializeTenancyBySubdomain::$onFail = function ($exception, $request, $next) {
            return $next($request);
        };
    }
}
