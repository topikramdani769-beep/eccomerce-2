<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. User Dummy
        User::create([
            'name'     => 'Test User',
            'email'    => 'user@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. Metode Pembayaran Dummy
        PaymentMethod::create([
            'name'           => 'Bank Transfer BCA',
            'code'           => 'bca_va',
            'account_number' => '1234567890',
            'account_holder' => 'PT BAPE INDONESIA',
            'instructions'   => 'Transfer sesuai nominal ke nomor rekening di atas.',
            'is_active'      => true,
        ]);

        PaymentMethod::create([
            'name'           => 'QRIS / GoPay / OVO',
            'code'           => 'qris',
            'account_number' => 'QRIS-BAPE-STORE',
            'account_holder' => 'BAPE STORE OFFICIAL',
            'instructions'   => 'Scan kode QRIS yang tersedia pada aplikasi e-wallet kamu.',
            'is_active'      => true,
        ]);

        // 3. Panggil Seeder Produk & Kategori
        $this->call([
            ProductSeeder::class,
        ]);
    }
}