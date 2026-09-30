<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::withCount('assets')->orderBy('name')->paginate(20);

        return view('pengurus.assets.categories', compact('categories'));
    }

    public function store(StoreAssetCategoryRequest $request)
    {
        AssetCategory::create($request->validated());

        return redirect()->route('pengurus.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, AssetCategory $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update($request->only('name', 'description', 'is_active'));

        return redirect()->route('pengurus.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(AssetCategory $category)
    {
        if ($category->assets()->exists()) {
            return redirect()->route('pengurus.categories.index')->with('error', 'Kategori tidak bisa dihapus karena masih memiliki aset.');
        }

        $category->delete();

        return redirect()->route('pengurus.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
