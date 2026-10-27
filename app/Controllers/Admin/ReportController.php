<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Validation\Validator;

final class ReportController
{
    /**
     * Received quantity and value per product, optionally for one category and a date range.
     */
    public function stock(Request $request): Response
    {
        $categoryId = $request->integer('category_id') ?: null;
        $from = Validator::isDate($request->string('from')) ? $request->string('from') : null;
        $to = Validator::isDate($request->string('to')) ? $request->string('to') : null;
        $rows = Product::stockSummary($categoryId, $from, $to);

        return view('admin/reports/stock', [
            'rows' => $rows,
            'totalValue' => array_sum(array_column($rows, 'value')),
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => ['category_id' => $categoryId, 'from' => $from, 'to' => $to],
        ]);
    }
}
