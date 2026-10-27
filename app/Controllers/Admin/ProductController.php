<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Unit;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Validation\ValidationException;

final class ProductController
{
    public function index(Request $request): Response
    {
        $search = $request->string('q');
        $categoryId = $request->integer('category_id');
        $query = Product::query()->search(Product::SEARCHABLE, $search);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->paginate(20, $request->integer('page', 1));

        return view('admin/products/index', [
            'products' => $products,
            'quantities' => StockEntry::quantitiesFor(array_map(static fn (Product $product) => $product->id, $products->items)),
            'units' => Unit::findMany(array_map(static fn (Product $product) => $product->unit_id, $products->items)),
            'categories' => Category::query()->orderBy('name')->get(),
            'search' => $search,
            'categoryId' => $categoryId,
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request): RedirectResponse
    {
        Product::create($request->validate($this->rules()));

        return to_route('admin.products.index')->with('success', __('products.created'));
    }

    public function edit(int $id): Response
    {
        return $this->form(Product::findOrFail($id));
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $data = $request->validate($this->rules($product));

        // Existing quantities were counted in the current unit, so it cannot change any more.
        if ((int) $data['unit_id'] !== $product->unit_id && $product->hasEntries()) {
            throw new ValidationException(['unit_id' => [__('products.unit_locked')]]);
        }

        $product->update($data);

        return to_route('admin.products.index')->with('success', __('products.updated'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        if ($product->hasEntries()) {
            return back()->with('error', __('products.has_entries'));
        }

        $product->delete();

        return back()->with('success', __('products.deleted'));
    }

    private function form(?Product $product): Response
    {
        return view('admin/products/form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    private function rules(?Product $product = null): array
    {
        return [
            'sku' => 'required|alpha_dash|max:55|unique:products,sku' . ($product ? ",{$product->id}" : ''),
            'name' => 'required|string|min:2|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'unit_id' => 'required|integer|exists:units,id',
            'description' => 'nullable|string|max:2000',
        ];
    }
}
