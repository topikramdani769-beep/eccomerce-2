<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Events\OrderCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        // 1. Ubah validasi: payment_method_id & shipping_address jadi nullable
        $request->validate([
            'payment_method_id' => 'nullable',
            'shipping_address'  => 'nullable|string',
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

        // 2. Set Konfigurasi Midtrans SDK
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        // Gunakan Database Transaction
        $orderData = DB::transaction(function () use ($user, $request, $carts) {
            // Hitung Total Belanja
            $total = $carts->sum(fn($item) => $item->product->price * $item->quantity);

            // Buat Record Order
            $order = Order::create([
                'user_id'           => $user->id,
                'payment_method_id' => $request->payment_method_id ?? null,
                'shipping_address'  => $request->shipping_address ?? 'Alamat Default',
                'order_number'      => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount'      => $total,
                'status'            => 'pending'
            ]);

            // Pindahkan item keranjang ke OrderItem & Potong Stok
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

            // Kosongkan keranjang user
            Cart::where('user_id', $user->id)->delete();

            // 3. Susun Payload untuk Midtrans Snap
            $params = [
                'transaction_details' => [
                    'order_id'     => $order->order_number,
                    'gross_amount' => (int) $total,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email'      => $user->email,
                ]
            ];

            // 4. Request Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Simpan Snap Token ke tabel order (jika kolom snap_token ada)
            $order->update(['snap_token' => $snapToken]);

            return [
                'order' => $order,
                'snap_token' => $snapToken
            ];
        });

        $order = $orderData['order'];
        $snapToken = $orderData['snap_token'];

        // Load relasi lengkap
        $order->load(['user', 'items.product', 'paymentMethod']);

        // Trigger Broadcast Event ke Admin 🚀
        event(new OrderCreated($order));

        // 5. Kembalikan Response beserta snap_token ke Vue
        return response()->json([
            'message'    => 'Checkout berhasil dibuat',
            'snap_token' => $snapToken,
            'order'      => $order
        ], 201);
    }

    // Riwayat Order Pengguna
    public function index(Request $request)
    {
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

        return response()->json($order->load(['items.product', 'paymentMethod']), 200);
    }
}