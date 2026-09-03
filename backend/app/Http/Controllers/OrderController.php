<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();
        $carts = Cart::with('product')->where('user_id', $user->id)->get();

        if ($carts->isEmpty()) {
            return response()->json(['message' => 'Keranjang belanja kosong'], 400);
        }

        // Hitung Total Belanjaan
        $total = $carts->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        // Buat Order Baru
        $order = Order::create([
            'user_id'      => $user->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'total_amount' => $total,
            'status'       => 'pending'
        ]);

        // Pindahkan item dari Cart ke OrderItem
        foreach ($carts as $cart) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $cart->product_id,
                'quantity'   => $cart->quantity,
                'price'      => $cart->product->price
            ]);
        }

        // Bersihkan keranjang user
        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Checkout berhasil dibuat',
            'order'   => $order->load('items.product')
        ], 201);
    }

    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($orders, 200);
    }
}