<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    public function stats()
    {
        $totalRevenue = Order::where('status', 'paid')->sum('total_amount');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<=', 5)->count();

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_orders'  => $totalOrders,
            'total_products'=> $totalProducts,
            'low_stock'     => $lowStockProducts,
        ], 200);
    }
}