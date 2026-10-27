<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\Unit;
use Framework\Auth\Auth;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;

final readonly class StockEntryController
{
    private const RULES = [
        'product_id' => 'required|integer|exists:products,id',
        'supplier_id' => 'nullable|integer|exists:suppliers,id',
        'quantity' => 'required|numeric|min:0.001|max:99999999',
        'unit_price' => 'required|numeric|min:0|max:99999999',
        'entry_date' => 'required|date',
        'document_no' => 'nullable|string|max:55',
        'note' => 'nullable|string|max:255',
    ];

    public function __construct(private Auth $auth)
    {
    }

    public function index(Request $request): Response
    {
        $query = StockEntry::filtered($request);
        $entries = (clone $query)->orderBy('entry_date', 'desc')->latest()->paginate(20, $request->integer('page', 1));

        return view('admin/entries/index', [
            'entries' => $entries,
            'relations' => StockEntry::relationsFor($entries->items),
            'filteredValue' => $query->sum('total_value'),
            'products' => Product::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'filters' => [
                'q' => $request->string('q'),
                'product_id' => $request->integer('product_id'),
                'supplier_id' => $request->integer('supplier_id'),
                'from' => $request->string('from'),
                'to' => $request->string('to'),
            ],
        ]);
    }

    /**
     * Download the filtered entries as a CSV file that opens in Excel.
     */
    public function export(Request $request): Response
    {
        $entries = StockEntry::filtered($request)->orderBy('entry_date', 'desc')->latest()->get();
        ['products' => $products, 'units' => $units, 'suppliers' => $suppliers, 'users' => $users] = StockEntry::relationsFor($entries);

        $csv = fopen('php://temp', 'r+');
        // The byte order mark makes Excel read the file as UTF-8 (Azerbaijani and Russian letters).
        fwrite($csv, "\u{FEFF}");
        fputcsv($csv, [
            __('entries.date'), __('products.sku'), __('entries.product'), __('entries.supplier'),
            __('entries.quantity'), __('units.unit'), __('entries.unit_price'), __('entries.total_value'),
            __('entries.document_no'), __('entries.note'), __('entries.recorded_by'),
        ]);

        foreach ($entries as $entry) {
            $product = $products[$entry->product_id] ?? null;

            fputcsv($csv, [
                $entry->entry_date->format('Y-m-d'),
                $product?->sku,
                $product?->name,
                $suppliers[$entry->supplier_id ?? 0]->name ?? '',
                $entry->quantity,
                $units[$product?->unit_id ?? 0]->symbol ?? '',
                // Plain numbers (no thousands separator) so spreadsheets can calculate with them.
                number_format($entry->unit_price, 2, '.', ''),
                number_format($entry->total_value, 2, '.', ''),
                $entry->document_no,
                $entry->note,
                $users[$entry->user_id ?? 0]->username ?? '',
            ]);
        }

        rewind($csv);
        $content = (string) stream_get_contents($csv);
        fclose($csv);

        return new Response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="stock-entries-' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function create(Request $request): Response
    {
        // ?product_id= preselects a product, e.g. from the products page.
        return $this->form(null, $request->integer('product_id') ?: null);
    }

    public function store(Request $request): RedirectResponse
    {
        $entry = (new StockEntry())->fill($request->validate(self::RULES));
        $entry->user_id = $this->auth->id();
        $entry->save();

        return to_route('admin.entries.index')->with('success', __('entries.created'));
    }

    public function edit(int $id): Response
    {
        return $this->form(StockEntry::findOrFail($id));
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        StockEntry::findOrFail($id)->update($request->validate(self::RULES));

        return to_route('admin.entries.index')->with('success', __('entries.updated'));
    }

    public function destroy(int $id): RedirectResponse
    {
        StockEntry::findOrFail($id)->delete();

        return back()->with('success', __('entries.deleted'));
    }

    private function form(?StockEntry $entry, ?int $productId = null): Response
    {
        $products = Product::query()->orderBy('name')->get();

        return view('admin/entries/form', [
            'entry' => $entry,
            'selectedProduct' => $entry?->product_id ?? $productId,
            'products' => $products,
            'units' => Unit::findMany(array_map(static fn (Product $product) => $product->unit_id, $products)),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
        ]);
    }
}
