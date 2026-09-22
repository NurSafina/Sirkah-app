<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:categories,name']]);
        Category::create(['name' => trim($data['name'])]);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)]]);
        $category->update(['name' => trim($data['name'])]);
        $category->products()->update(['category' => trim($data['name'])]);
        return back()->with('success', 'Kategori berhasil diperbarui.');
    }
}
