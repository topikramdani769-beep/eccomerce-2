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
            'quantity'   => 'required|integer|min:1'
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id'    => $request->user()->id,
                'product_id' => $request->product_id
            ],
            [
                'quantity'   => $request->quantity
            ]
        );

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