<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori
        $categories = [
            ['name' => 'T-Shirts', 'slug' => 't-shirts'],
            ['name' => 'Hoodies & Jackets', 'slug' => 'hoodies-jackets'],
            ['name' => 'Footwear', 'slug' => 'footwear'],
            ['name' => 'Accessories', 'slug' => 'accessories'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 2. Buat Produk Dummy
        $products = [
            [
                'category_id' => 1,
                'name'        => 'AAPE College Tee Black',
                'price'       => 1200000,
                'stock'       => 15,
                'image'       => 'https://via.placeholder.com/600x600?text=AAPE+Tee+Black',
            ],
            [
                'category_id' => 1,
                'name'        => 'Camo Shark Head Tee White',
                'price'       => 1350000,
                'stock'       => 10,
                'image'       => 'https://via.placeholder.com/600x600?text=Shark+Tee+White',
            ],
            [
                'category_id' => 2,
                'name'        => 'Full Zip Camo Shark Hoodie Green',
                'price'       => 5500000,
                'stock'       => 5,
                'image'       => 'https://via.placeholder.com/600x600?text=Shark+Hoodie+Green',
            ],
            [
                'category_id' => 3,
                'name'        => 'Bapesta Low ABC Camo Green',
                'price'       => 4200000,
                'stock'       => 8,
                'image'       => 'https://via.placeholder.com/600x600?text=Bapesta+Low+Green',
            ],
            [
                'category_id' => 4,
                'name'        => 'AAPE Camo Shoulder Bag',
                'price'       => 850000,
                'stock'       => 20,
                'image'       => 'https://via.placeholder.com/600x600?text=Camo+Bag',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}