<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Supplier;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;

final class SupplierController
{
    public function index(Request $request): Response
    {
        $search = $request->string('q');
        $suppliers = Supplier::query()->search(Supplier::SEARCHABLE, $search)->orderBy('name')->paginate(20, $request->integer('page', 1));

        return view('admin/suppliers/index', ['suppliers' => $suppliers, 'search' => $search]);
    }

    public function create(): Response
    {
        return view('admin/suppliers/form', ['supplier' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($request->validate($this->rules()));

        return to_route('admin.suppliers.index')->with('success', __('suppliers.created'));
    }

    public function edit(int $id): Response
    {
        return view('admin/suppliers/form', ['supplier' => Supplier::findOrFail($id)]);
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->validate($this->rules($supplier)));

        return to_route('admin.suppliers.index')->with('success', __('suppliers.updated'));
    }

    /**
     * Past entries of a deleted supplier stay, without a supplier (ON DELETE SET NULL).
     */
    public function destroy(int $id): RedirectResponse
    {
        Supplier::findOrFail($id)->delete();

        return back()->with('success', __('suppliers.deleted'));
    }

    private function rules(?Supplier $supplier = null): array
    {
        return [
            'name' => 'required|string|min:2|max:100|unique:suppliers,name' . ($supplier ? ",{$supplier->id}" : ''),
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
        ];
    }
}
