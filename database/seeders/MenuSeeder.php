<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Cappuccino',
                'description' => 'Espresso dengan susu berbusa yang lembut',
                'price' => 25000,
                'category' => 'Minuman',
                'image_url' => 'https://images.unsplash.com/photo-1708430651927-20e2e1f1e8f7?auto=format&fit=crop&w=800&q=80',
                'is_popular' => true,
                'rating' => 4.8,
            ],
            [
                'name' => 'Espresso',
                'description' => 'Kopi murni dengan rasa yang kuat',
                'price' => 18000,
                'category' => 'Minuman',
                'image_url' => 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=800&q=80',
                'rating' => 4.9,
            ],
            [
                'name' => 'Latte',
                'description' => 'Espresso dengan susu steamed yang creamy',
                'price' => 28000,
                'category' => 'Minuman',
                'image_url' => 'https://images.unsplash.com/photo-1582152747136-af63c112fce5?auto=format&fit=crop&w=800&q=80',
                'is_popular' => true,
                'rating' => 4.7,
            ],
            [
                'name' => 'Americano',
                'description' => 'Espresso dengan air panas',
                'price' => 22000,
                'category' => 'Minuman',
                'image_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80',
                'rating' => 4.6,
            ],
            [
                'name' => 'Ice Coffee',
                'description' => 'Kopi dingin yang menyegarkan',
                'price' => 26000,
                'category' => 'Minuman',
                'image_url' => 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?auto=format&fit=crop&w=800&q=80',
                'is_new' => true,
                'rating' => 4.5,
            ],
            [
                'name' => 'Mocha',
                'description' => 'Perpaduan espresso, coklat, dan susu',
                'price' => 30000,
                'category' => 'Minuman',
                'image_url' => 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=800&q=80',
                'rating' => 4.7,
            ],
            [
                'name' => 'Croissant',
                'description' => 'Roti khas Perancis yang renyah dan lembut',
                'price' => 20000,
                'category' => 'Makanan',
                'image_url' => 'https://images.unsplash.com/photo-1712723247648-64a03ba7c333?auto=format&fit=crop&w=800&q=80',
                'is_popular' => true,
                'rating' => 4.6,
            ],
            [
                'name' => 'Sandwich',
                'description' => 'Sandwich segar dengan berbagai pilihan isian',
                'price' => 35000,
                'category' => 'Makanan',
                'image_url' => 'https://images.unsplash.com/photo-1642335381031-8c80a25d1bbd?auto=format&fit=crop&w=800&q=80',
                'rating' => 4.5,
            ],
            [
                'name' => 'Muffin',
                'description' => 'Kue muffin dengan berbagai varian rasa',
                'price' => 18000,
                'category' => 'Makanan',
                'image_url' => 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?auto=format&fit=crop&w=800&q=80',
                'rating' => 4.4,
            ],
            [
                'name' => 'Bagel',
                'description' => 'Roti bagel dengan cream cheese',
                'price' => 25000,
                'category' => 'Makanan',
                'image_url' => 'https://images.unsplash.com/photo-1599599810769-bcde5a160d32?auto=format&fit=crop&w=800&q=80',
                'is_new' => true,
                'rating' => 4.5,
            ],
            [
                'name' => 'Platter Premium',
                'description' => 'Kombinasi croissant, muffin, dan cookie',
                'price' => 50000,
                'category' => 'Platters',
                'image_url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80',
                'is_popular' => true,
                'rating' => 4.8,
            ],
            [
                'name' => 'Platter Breakfast',
                'description' => 'Paket sarapan lengkap dengan kopi',
                'price' => 45000,
                'category' => 'Platters',
                'image_url' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?auto=format&fit=crop&w=800&q=80',
                'rating' => 4.7,
            ],
            [
                'name' => 'Platter Snack',
                'description' => 'Aneka snack untuk menemani kopi',
                'price' => 40000,
                'category' => 'Platters',
                'image_url' => 'https://images.unsplash.com/photo-1559386484-97dfc0e15539?auto=format&fit=crop&w=800&q=80',
                'rating' => 4.6,
            ],
        ];

        foreach ($items as $i => $item) {
            $slug = Str::slug($item['name']);

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'description' => $item['description'] ?? null,
                    'price' => $item['price'] ?? 0,
                    'image_url' => $item['image_url'] ?? null,
                    'is_active' => true,
                    'is_popular' => (bool)($item['is_popular'] ?? false),
                    'is_new' => (bool)($item['is_new'] ?? false),
                    'rating' => $item['rating'] ?? null,
                    'sort_order' => $i + 1,
                ]
            );
        }
    }
}
