<?php

declare(strict_types=1);

use App\Controllers\Api\StockController;
use Framework\Routing\Router;

return function (Router $router): void {
    // Uses the same session login as the dashboard; guests get 401, missing permissions 403.
    $router->group(['prefix' => 'api', 'as' => 'api.', 'middleware' => ['api', 'auth', 'can']], function (Router $router): void {
        $router->get('/stock', [StockController::class, 'stock'])->name('stock')->middleware('can:view_reports');
        $router->get('/entries', [StockController::class, 'entries'])->name('entries.index')->middleware('can:view_entries');
        $router->get('/entries/{id:\d+}', [StockController::class, 'entry'])->name('entries.show')->middleware('can:view_entries');
    });
};
