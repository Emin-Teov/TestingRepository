<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Unit;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;

final class UnitController
{
    public function index(Request $request): Response
    {
        $search = $request->string('q');
        $units = Unit::query()->search(['name', 'symbol'], $search)->orderBy('name')->paginate(20, $request->integer('page', 1));

        return view('admin/units/index', ['units' => $units, 'search' => $search]);
    }

    public function create(): Response
    {
        return view('admin/units/form', ['unit' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Unit::create($request->validate($this->rules()));

        return to_route('admin.units.index')->with('success', __('units.created'));
    }

    public function edit(int $id): Response
    {
        return view('admin/units/form', ['unit' => Unit::findOrFail($id)]);
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $unit = Unit::findOrFail($id);
        $unit->update($request->validate($this->rules($unit)));

        return to_route('admin.units.index')->with('success', __('units.updated'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $unit = Unit::findOrFail($id);

        if ($unit->isUsed()) {
            return back()->with('error', __('units.in_use'));
        }

        $unit->delete();

        return back()->with('success', __('units.deleted'));
    }

    private function rules(?Unit $unit = null): array
    {
        $ignore = $unit ? ",{$unit->id}" : '';

        return [
            'name' => 'required|string|min:1|max:55|unique:units,name' . $ignore,
            'symbol' => 'required|string|min:1|max:15|unique:units,symbol' . $ignore,
        ];
    }
}
