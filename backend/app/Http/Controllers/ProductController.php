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
        $query = Product::with(['category', 'images']);

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
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // 1. Simpan Gambar Utama (Main Image)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        unset($validated['images']);

        // Buat Produk Baru
        $product = Product::create($validated);

        // 2. Simpan Gambar Tambahan (Galeri) ke tabel relasi product_images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $extraPath = $file->store('products', 'public');
                $product->images()->create([
                    'image_path' => url('storage/' . $extraPath)
                ]);
            }
        }

        // Load kembali dengan relasi gambar
        $product->load('images');

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'product' => $product
        ], 201);
    }

    // Tampilkan Detail Produk
    public function show($id)
    {
        // Wajib menyertakan ->with('images') agar galeri ikut terkirim ke Vue
        $product = Product::with(['category', 'images'])->findOrFail($id);
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
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Update Gambar Utama jika ada file baru
        if ($request->hasFile('image')) {
            if ($product->image && str_contains($product->image, 'storage/products/')) {
                $oldPath = str_replace(url('storage/'), '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        unset($validated['images']);
        $product->update($validated);

        // Jika ada tambahan gambar galeri baru saat update
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $extraPath = $file->store('products', 'public');
                $product->images()->create([
                    'image_path' => url('storage/' . $extraPath)
                ]);
            }
        }

        $product->load('images');

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'product' => $product
        ], 200);
    }

    // Hapus Produk
    public function destroy($id)
    {
        $product = Product::with('images')->findOrFail($id);

        // Hapus gambar utama fisik
        if ($product->image && str_contains($product->image, 'storage/products/')) {
            $oldPath = str_replace(url('storage/'), '', $product->image);
            Storage::disk('public')->delete($oldPath);
        }

        // Hapus file gambar galeri fisik dan data relasinya
        foreach ($product->images as $img) {
            if ($img->image_path && str_contains($img->image_path, 'storage/products/')) {
                $oldExtraPath = str_replace(url('storage/'), '', $img->image_path);
                Storage::disk('public')->delete($oldExtraPath);
            }
            $img->delete();
        }

        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus'
        ], 200);
    }
}