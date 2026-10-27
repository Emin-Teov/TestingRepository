<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use Framework\Http\Response;

final class DashboardController
{
    public function index(): Response
    {
        $monthStart = date('Y-m-01');
        $recent = StockEntry::query()->orderBy('entry_date', 'desc')->latest()->limit(6)->get();

        return view('admin/dashboard', [
            'stats' => [
                'products' => Product::count(),
                'stock_value' => StockEntry::query()->sum('total_value'),
                'entries_this_month' => StockEntry::query()->where('entry_date', '>=', $monthStart)->count(),
                'value_this_month' => StockEntry::query()->where('entry_date', '>=', $monthStart)->sum('total_value'),
                'suppliers' => Supplier::count(),
            ],
            'recent' => $recent,
            'relations' => StockEntry::relationsFor($recent),
            'activity' => $this->dailyValue(),
        ]);
    }

    /**
     * Value of goods received on each of the last 7 days, oldest first.
     *
     * @return array<string, float> Y-m-d => value
     */
    private function dailyValue(): array
    {
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $days[date('Y-m-d', strtotime("-$i days"))] = 0.0;
        }

        $rows = StockEntry::query()->where('entry_date', '>=', array_key_first($days))->select('entry_date', 'total_value')->get();

        foreach ($rows as $entry) {
            $day = $entry->entry_date->format('Y-m-d');

            if (isset($days[$day])) {
                $days[$day] += $entry->total_value;
            }
        }

        return $days;
    }
}
