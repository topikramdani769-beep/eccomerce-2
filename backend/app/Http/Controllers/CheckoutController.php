<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        // 1. Set Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        DB::beginTransaction();
        try {
            $user = auth()->user();

            // 2. Buat Record Order Utama
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'TRX-' . time() . rand(100, 999),
                'total_price' => $request->total_price,
                'status' => 'pending',
            ]);

            // 3. Simpan Item Keranjang
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'] ?? $item['product']['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['product']['price'],
                ]);
            }

            // 4. Hapus Keranjang User
            Cart::where('user_id', $user->id)->delete();

            // 5. Buat Payload Snap Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) $request->total_price,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ];

            // 6. Request Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($params);

            // 7. Simpan Snap Token ke Order
            $order->update(['snap_token' => $snapToken]);

            DB::commit();

            return response()->json([
                'message' => 'Order berhasil dibuat',
                'snap_token' => $snapToken,
                'order_number' => $order->order_number
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}