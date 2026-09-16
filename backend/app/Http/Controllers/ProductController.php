<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Fetch Produk dengan Filter & Search
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

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
            'size'        => 'nullable|string',
            'description' => 'nullable|string',
            'images'      => 'nullable|array', // Harus ada deklarasi array untuk induknya
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image'       => 'nullable',
        ]);

        // Fleksibel: Deteksi file baik dari 'images' (array Vue) maupun 'image' (single)
        $file = null;
        if ($request->hasFile('images')) {
            $file = $request->file('images')[0];
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
        }

        if ($file) {
            $path = $file->store('products', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        // Clean up array images dari payload agar tidak masuk ke mass-assignment Eloquent jika kolomnya tidak ada
        unset($validated['images']);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'product' => $product
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product, 200);
    }

    // Update Produk
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'size'        => 'nullable|string',
            'description' => 'nullable|string',
            'images'      => 'nullable|array', // Deklarasi tipe array
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image'       => 'nullable',
        ]);

        // Fleksibel: Deteksi file baru yang diunggah
        $file = null;
        if ($request->hasFile('images')) {
            $file = $request->file('images')[0];
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
        }

        if ($file) {
            // Hapus gambar lama jika tersimpan di local storage
            if ($product->image && str_contains($product->image, 'storage/products/')) {
                $oldPath = str_replace(url('storage/'), '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $file->store('products', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        unset($validated['images']);

        $product->update($validated);

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'product' => $product
        ], 200);
    }

    // Hapus Produk
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

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