<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Events\OrderCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'shipping_address'  => 'required|string',
        ]);

        $user = $request->user();
        $carts = Cart::with('product')->where('user_id', $user->id)->get();

        if ($carts->isEmpty()) {
            return response()->json(['message' => 'Keranjang belanja kosong'], 400);
        }

        // Check ketersediaan stok sebelum checkout
        foreach ($carts as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => "Stok produk {$item->product->name} tidak mencukupi"
                ], 400);
            }
        }

        // Gunakan Database Transaction agar aman dari error saat pemprosesan
        $order = DB::transaction(function () use ($user, $request, $carts) {
            // Hitung Total Belanja
            $total = $carts->sum(fn($item) => $item->product->price * $item->quantity);

            // 1. Buat Order
            $order = Order::create([
                'user_id'           => $user->id,
                'payment_method_id' => $request->payment_method_id,
                'shipping_address'  => $request->shipping_address,
                'order_number'      => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount'      => $total,
                'status'            => 'pending'
            ]);

            // 2. Pindahkan item keranjang ke OrderItem & Potong Stok
            foreach ($carts as $cart) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity'   => $cart->quantity,
                    'price'      => $cart->product->price
                ]);

                // Potong Stok Produk
                $cart->product->decrement('stock', $cart->quantity);
            }

            // 3. Kosongkan keranjang user
            Cart::where('user_id', $user->id)->delete();

            return $order;
        });

        // 4. Load relasi lengkap (menggunakan 'items' atau 'orderItems' sesuai model)
        $order->load(['user', 'items.product', 'paymentMethod']);

        // 5. Trigger Broadcast Event ke Admin 🚀
        event(new OrderCreated($order));

        return response()->json([
            'message' => 'Checkout berhasil dibuat',
            'order'   => $order
        ], 201);
    }

    // Riwayat Order Pengguna
    public function index(Request $request)
    {
        // Mengubah 'orderItems.product' menjadi 'items.product'
        $orders = Order::with(['items.product', 'paymentMethod'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($orders, 200);
    }

    // Detail Order Spesifik
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mengubah 'orderItems.product' menjadi 'items.product'
        return response()->json($order->load(['items.product', 'paymentMethod']), 200);
    }
}       