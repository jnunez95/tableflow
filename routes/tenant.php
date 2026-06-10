<?php

declare(strict_types=1);

use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'tenant' => tenant()?->only(['id', 'name']),
            'tableUuid' => request()->query('table'),
        ]);
    })->name('tenant.welcome');

    Route::get('/menu', function () {
        return Inertia::render('Menu', [
            'tableUuid' => request()->query('table'),
            'tenant' => tenant()?->only(['id', 'name']),
        ]);
    })->name('tenant.menu');

    Route::get('/bill', function () {
        return Inertia::render('Resume', [
            'tableUuid' => request()->query('table'),
            'tenant' => tenant()?->only(['id', 'name']),
        ]);
    })->name('tenant.bill');

    Route::prefix('api')->group(function () {
        Route::get('/menu/categories', [MenuController::class, 'categories']);
        Route::get('/menu/products', [MenuController::class, 'products']);
        Route::get('/menu/products/{product}', [MenuController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/tables/{table}', [TableController::class, 'show']);
        Route::get('/tables/{table}/bill', [OrderController::class, 'getBillByTable']);
        Route::post('/tables/{table}/close-bill', [OrderController::class, 'closeBillByTable']);
    });
});
