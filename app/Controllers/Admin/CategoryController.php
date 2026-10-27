<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Category;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;

final class CategoryController
{
    public function index(Request $request): Response
    {
        $search = $request->string('q');
        $categories = Category::query()->search(['name'], $search)->orderBy('name')->paginate(20, $request->integer('page', 1));

        return view('admin/categories/index', ['categories' => $categories, 'search' => $search]);
    }

    public function create(): Response
    {
        return view('admin/categories/form', ['category' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($request->validate($this->rules()));

        return to_route('admin.categories.index')->with('success', __('categories.created'));
    }

    public function edit(int $id): Response
    {
        return view('admin/categories/form', ['category' => Category::findOrFail($id)]);
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $category->update($request->validate($this->rules($category)));

        return to_route('admin.categories.index')->with('success', __('categories.updated'));
    }

    /**
     * Products of a deleted category stay, without a category (ON DELETE SET NULL).
     */
    public function destroy(int $id): RedirectResponse
    {
        Category::findOrFail($id)->delete();

        return back()->with('success', __('categories.deleted'));
    }

    private function rules(?Category $category = null): array
    {
        return ['name' => 'required|string|min:2|max:100|unique:categories,name' . ($category ? ",{$category->id}" : '')];
    }
}
