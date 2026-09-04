<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Fetch Produk dengan Filter, Search, & Pagination
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filter berdasarkan pencarian nama
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan category_id
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->get();

        return response()->json($products, 200);
    }

    // Tambah Produk
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable', // BISA FILE / STRING URL
        ]);

        // Cek apakah ada file gambar yang di-upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'product' => $product
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'), 200);
    }

    // Update Produk
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'image'       => 'nullable',
        ]);

        // Handle upload gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika tersimpan di local storage
            if ($product->image && str_contains($product->image, 'storage/products/')) {
                $oldPath = str_replace(url('storage/'), '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'product' => $product
        ], 200);
    }

    // Hapus Produk
    public function destroy(Product $product)
    {
        // Hapus file gambar dari storage jika ada
        if ($product->image && str_contains($product->image, 'storage/products/')) {
            $oldPath = str_replace(url('storage/'), '', $product->image);
            Storage::disk('public')->delete($oldPath);
        }

        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus'
        ], 200);
    }
}