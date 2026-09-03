<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // Mengambil daftar wishlist milik user yang sedang login
    public function index(Request $request)
    {
        $wishlist = Wishlist::with('product.category')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($wishlist, 200);
    }

    // Menambah atau Menghapus Wishlist (Toggle)
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = $request->user()->id;
        $productId = $request->product_id;

        $existingWishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existingWishlist) {
            // Jika sudah ada di wishlist, hapus (toggle off)
            $existingWishlist->delete();

            return response()->json([
                'message' => 'Produk dihapus dari wishlist',
                'is_wishlist' => false
            ], 200);
        }

        // Jika belum ada, tambahkan (toggle on)
        $wishlist = Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'message' => 'Produk ditambahkan ke wishlist',
            'is_wishlist' => true,
            'wishlist' => $wishlist
        ], 201);
    }

    // Menghapus item dari wishlist berdasarkan ID Wishlist
    public function destroy(Request $request, $id)
    {
        Wishlist::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus dari wishlist'
        ], 200);
    }
}