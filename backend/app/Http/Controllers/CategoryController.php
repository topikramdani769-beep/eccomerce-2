<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // 1. Lihat Semua Kategori
    public function index()
    {
        return response()->json(Category::all(), 200);
    }

    // 2. Tambah Kategori Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'message'  => 'Kategori berhasil ditambahkan',
            'category' => $category
        ], 201);
    }

    // 3. Lihat Detail Single Kategori
    public function show(Category $category)
    {
        return response()->json($category, 200);
    }

    // 4. Edit / Update Kategori
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'message'  => 'Kategori berhasil diperbarui',
            'category' => $category
        ], 200);
    }

    // 5. Hapus Kategori
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus'
        ], 200);
    }
}