<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Product;
use App\Models\StockEntry;
use Framework\Http\JsonResponse;
use Framework\Http\Request;

/**
 * Read-only JSON API for signed-in users: /api/stock, /api/entries, /api/entries/{id}
 */
final class StockController
{
    public function stock(): JsonResponse
    {
        return json(['data' => Product::stockSummary()]);
    }

    public function entries(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, $request->integer('per_page', 20)));

        return json(StockEntry::filtered($request)->orderBy('entry_date', 'desc')->latest()->paginate($perPage, $request->integer('page', 1)));
    }

    public function entry(int $id): JsonResponse
    {
        return json(['data' => StockEntry::findOrFail($id)]);
    }
}
