<?php

declare(strict_types=1);

use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\ReportController;
use App\Controllers\Admin\StockEntryController;
use App\Controllers\Admin\SupplierController;
use App\Controllers\Admin\UnitController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\UserTypeController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\LocaleController;
use Framework\Routing\Router;

return function (Router $router): void {
    $router->group(['middleware' => 'web'], function (Router $router): void {
        $router->get('/', [HomeController::class, 'index'])->name('home');
        $router->post('/locale/{locale:[a-z]{2}}', [LocaleController::class, 'update'])->name('locale');

        $router->group(['middleware' => 'guest'], function (Router $router): void {
            $router->get('/login', [AuthController::class, 'create'])->name('login');
            $router->post('/login', [AuthController::class, 'store'])->name('login.store');
        });

        $router->post('/logout', [AuthController::class, 'destroy'])->name('logout')->middleware('auth');

        // Every signed-in, active user sees the dashboard; everything else depends on the
        // permissions of their user type (see App\Enums\Permission and the usertypes table).
        $router->group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'can']], function (Router $router): void {
            $router->get('/', [DashboardController::class, 'index'])->name('dashboard');

            $router->get('/entries', [StockEntryController::class, 'index'])->name('entries.index')->middleware('can:view_entries');
            $router->get('/entries/export', [StockEntryController::class, 'export'])->name('entries.export')->middleware('can:view_entries');
            $router->get('/entries/create', [StockEntryController::class, 'create'])->name('entries.create')->middleware('can:create_entries');
            $router->post('/entries', [StockEntryController::class, 'store'])->name('entries.store')->middleware('can:create_entries');
            $router->get('/entries/{id:\d+}/edit', [StockEntryController::class, 'edit'])->name('entries.edit')->middleware('can:edit_entries');
            $router->put('/entries/{id:\d+}', [StockEntryController::class, 'update'])->name('entries.update')->middleware('can:edit_entries');
            $router->delete('/entries/{id:\d+}', [StockEntryController::class, 'destroy'])->name('entries.destroy')->middleware('can:delete_entries');

            $router->group(['middleware' => 'can:manage_products'], function (Router $router): void {
                $router->resource('products', ProductController::class);
                $router->resource('categories', CategoryController::class);
                $router->resource('units', UnitController::class);
            });

            $router->group(['middleware' => 'can:manage_suppliers'], function (Router $router): void {
                $router->resource('suppliers', SupplierController::class);
            });

            $router->get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock')->middleware('can:view_reports');

            $router->group(['middleware' => 'can:manage_users'], function (Router $router): void {
                $router->resource('users', UserController::class);
            });

            $router->group(['middleware' => 'can:manage_usertypes'], function (Router $router): void {
                $router->resource('usertypes', UserTypeController::class);
            });
        });
    });
};
