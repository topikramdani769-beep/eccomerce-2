<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($cart, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'size'       => 'nullable|string'
        ]);

        $userId = $request->user()->id;
        $productId = $request->product_id;
        $size = $request->size;
        $quantity = $request->quantity;

        // Cek apakah item dengan produk dan ukuran yang sama sudah ada di keranjang user
        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('size', $size)
            ->first();

        if ($cart) {
            // Jika sudah ada, tambahkan quantity-nya
            $cart->quantity += $quantity;
            $cart->save();
        } else {
            // Jika belum ada, buat baru
            $cart = Cart::create([
                'user_id'    => $userId,
                'product_id' => $productId,
                'size'       => $size,
                'quantity'   => $quantity
            ]);
        }

        return response()->json([
            'message' => 'Item berhasil dimasukkan ke keranjang',
            'cart'    => $cart
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $cart->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'message' => 'Jumlah item berhasil diperbarui',
            'cart'    => $cart
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        Cart::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        return response()->json([
            'message' => 'Item keranjang berhasil dihapus'
        ], 200);
    }
}