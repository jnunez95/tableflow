<?php

declare(strict_types=1);

use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\KitchenController;
use App\Models\Company;
use App\Models\Table as DiningTable;
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
        $usesQrCode = Company::usesQrCode();

        return Inertia::render('Welcome', [
            'tenant' => tenant()?->only(['id', 'name']),
            'tableUuid' => request()->query('table'),
            'usesQrCode' => $usesQrCode,
            'tables' => $usesQrCode
                ? []
                : DiningTable::query()
                    ->orderBy('number')
                    ->get(['uuid', 'number'])
                    ->values(),
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

    Route::get('/kitchen', function () {
        return Inertia::render('Kitchen', [
            'tenant' => tenant()?->only(['id', 'name']),
        ]);
    })->name('tenant.kitchen');

    Route::get('/order/verify', function () {
        return redirect()->route('tenant.menu', [
            'table' => request()->query('table'),
        ]);
    })->name('tenant.order.verify.redirect');

    Route::post('/order/verify', function () {
        return Inertia::render('OrderVerify', [
            'tableUuid' => request()->input('table_uuid'),
            'tenant' => tenant()?->only(['id', 'name']),
            'items' => request()->input('items', []),
        ]);
    })->name('tenant.order.verify');

    Route::prefix('api')->group(function () {
        Route::get('/menu/categories', [MenuController::class, 'categories']);
        Route::get('/menu/products', [MenuController::class, 'products']);
        Route::get('/menu/products/{product}', [MenuController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/tables/{table}', [TableController::class, 'show']);
        Route::get('/tables/{table}/bill', [OrderController::class, 'getBillByTable']);
        Route::post('/tables/{table}/close-bill', [OrderController::class, 'closeBillByTable']);
        Route::get('/kitchen/orders', [KitchenController::class, 'index']);
        Route::put('/kitchen/orders/{order}/ready', [KitchenController::class, 'markOrderReady']);
        Route::put('/kitchen/items/{item}/toggle', [KitchenController::class, 'toggleItemReady']);
    });
});
