<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // 1. Import Str di sini

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::all(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        // 2. Tambahkan pembuatan slug otomatis
        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan',
            'category' => $category
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        // 3. Update nama beserta slug-nya
        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui',
            'category' => $category
        ], 200);
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Gagal menghapus! Masih ada produk yang menggunakan kategori ini.'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus'
        ], 200);
    }
}