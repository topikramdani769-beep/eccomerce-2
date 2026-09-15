<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        // Mengambil semua payment method tanpa memfilter status is_active
        $methods = PaymentMethod::all();
        return response()->json($methods, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|unique:payment_methods,code',
            'account_number' => 'nullable|string',
            'account_holder' => 'nullable|string',
            'instructions'   => 'nullable|string',
        ]);

        $data = $request->all();
        // Set default is_active ke true jika tidak diisi
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        $paymentMethod = PaymentMethod::create($data);

        return response()->json([
            'message'        => 'Metode pembayaran berhasil ditambahkan',
            'payment_method' => $paymentMethod
        ], 201);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'name'           => 'sometimes|string|max:255',
            'code'           => 'sometimes|string|unique:payment_methods,code,' . $paymentMethod->id,
            'account_number' => 'nullable|string',
            'account_holder' => 'nullable|string',
            'instructions'   => 'nullable|string',
            'is_active'      => 'sometimes|boolean',
        ]);

        $paymentMethod->update($request->all());

        return response()->json([
            'message'        => 'Metode pembayaran diperbarui',
            'payment_method' => $paymentMethod
        ], 200);
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return response()->json([
            'message' => 'Metode pembayaran dihapus'
        ], 200);
    }
}