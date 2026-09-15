<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::with('category')->get());
    }

    public function show($id)
    {
        return response()->json(Product::with('category')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric',
            'stock'       => 'required|integer',
            'size'        => 'nullable|string',
            'description' => 'nullable|string',
            'images'      => 'nullable|array',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Cek jika ada file yang dikirim via key images[] dari Vue
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            // Ambil file pertama
            $path = $files[0]->store('products', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name'        => 'sometimes|required|string|max:255',
            'price'       => 'sometimes|required|numeric',
            'stock'       => 'sometimes|required|integer',
            'size'        => 'nullable|string',
            'description' => 'nullable|string',
            'images'      => 'nullable|array',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Jika ada file gambar baru yang diunggah
        if ($request->hasFile('images')) {
            // Hapus gambar lama jika ada
            if ($product->image) {
                $oldPath = str_replace(url('storage/'), '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }

            $files = $request->file('images');
            $path = $files[0]->store('products', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        $product->update($validated);
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            $oldPath = str_replace(url('storage/'), '', $product->image);
            Storage::disk('public')->delete($oldPath);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}