<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isAdmin(), 403);
        $name = $request->input('name');
        if (\is_string($name)) {
            $request->merge(['name' => trim($name)]);
        }

        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:categories,name']]);
        Category::create(['name' => $data['name']]);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isAdmin(), 403);
        $name = $request->input('name');
        if (\is_string($name)) {
            $request->merge(['name' => trim($name)]);
        }

        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->getKey())]]);
        $category->update(['name' => $data['name']]);
        $category->products()->update(['category' => $data['name']]);
        return back()->with('success', 'Kategori berhasil diperbarui.');
    }
}
