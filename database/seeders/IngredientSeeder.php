<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $ingredients = [
            ['name' => 'Kopi Arabica', 'unit' => 'gram', 'stock' => 5000, 'min_stock' => 500],
            ['name' => 'Susu UHT', 'unit' => 'ml', 'stock' => 10000, 'min_stock' => 1000],
            ['name' => 'Air Mineral', 'unit' => 'ml', 'stock' => 20000, 'min_stock' => 2000],
            ['name' => 'Cokelat Bubuk', 'unit' => 'gram', 'stock' => 3000, 'min_stock' => 300],
            ['name' => 'Gula Pasir', 'unit' => 'gram', 'stock' => 5000, 'min_stock' => 500],
            ['name' => 'Es Batu', 'unit' => 'gram', 'stock' => 5000, 'min_stock' => 500],
            ['name' => 'Tepung Terigu', 'unit' => 'gram', 'stock' => 10000, 'min_stock' => 1000],
            ['name' => 'Mentega', 'unit' => 'gram', 'stock' => 5000, 'min_stock' => 500],
            ['name' => 'Ragi', 'unit' => 'gram', 'stock' => 1000, 'min_stock' => 100],
            ['name' => 'Telur', 'unit' => 'buah', 'stock' => 200, 'min_stock' => 20],
            ['name' => 'Roti Gandum', 'unit' => 'lembar', 'stock' => 200, 'min_stock' => 20],
            ['name' => 'Selada', 'unit' => 'gram', 'stock' => 1000, 'min_stock' => 100],
            ['name' => 'Mayones', 'unit' => 'ml', 'stock' => 2000, 'min_stock' => 200],
            ['name' => 'Daging Ayam', 'unit' => 'gram', 'stock' => 3000, 'min_stock' => 300],
            ['name' => 'Cream Cheese', 'unit' => 'gram', 'stock' => 1500, 'min_stock' => 150],
            ['name' => 'Biskuit', 'unit' => 'pcs', 'stock' => 200, 'min_stock' => 20],
        ];

        foreach ($ingredients as $ingredientData) {
            Ingredient::updateOrCreate(
                ['name' => $ingredientData['name']],
                [
                    'unit' => $ingredientData['unit'],
                    'stock' => $ingredientData['stock'],
                    'min_stock' => $ingredientData['min_stock'],
                ]
            );
        }

        $recipes = [
            'Cappuccino' => [
                ['name' => 'Kopi Arabica', 'qty' => 10],
                ['name' => 'Susu UHT', 'qty' => 100],
                ['name' => 'Gula Pasir', 'qty' => 5],
            ],
            'Espresso' => [
                ['name' => 'Kopi Arabica', 'qty' => 12],
                ['name' => 'Air Mineral', 'qty' => 60],
            ],
            'Latte' => [
                ['name' => 'Kopi Arabica', 'qty' => 10],
                ['name' => 'Susu UHT', 'qty' => 120],
            ],
            'Americano' => [
                ['name' => 'Kopi Arabica', 'qty' => 15],
                ['name' => 'Air Mineral', 'qty' => 100],
            ],
            'Ice Coffee' => [
                ['name' => 'Kopi Arabica', 'qty' => 12],
                ['name' => 'Susu UHT', 'qty' => 80],
                ['name' => 'Es Batu', 'qty' => 50],
            ],
            'Mocha' => [
                ['name' => 'Kopi Arabica', 'qty' => 10],
                ['name' => 'Susu UHT', 'qty' => 100],
                ['name' => 'Cokelat Bubuk', 'qty' => 8],
            ],
            'Croissant' => [
                ['name' => 'Tepung Terigu', 'qty' => 50],
                ['name' => 'Mentega', 'qty' => 15],
                ['name' => 'Ragi', 'qty' => 2],
            ],
            'Sandwich' => [
                ['name' => 'Roti Gandum', 'qty' => 2],
                ['name' => 'Daging Ayam', 'qty' => 30],
                ['name' => 'Selada', 'qty' => 10],
                ['name' => 'Mayones', 'qty' => 10],
            ],
            'Muffin' => [
                ['name' => 'Tepung Terigu', 'qty' => 40],
                ['name' => 'Telur', 'qty' => 1],
                ['name' => 'Gula Pasir', 'qty' => 10],
            ],
            'Bagel' => [
                ['name' => 'Tepung Terigu', 'qty' => 50],
                ['name' => 'Ragi', 'qty' => 2],
                ['name' => 'Cream Cheese', 'qty' => 15],
            ],
            'Platter Premium' => [
                ['name' => 'Tepung Terigu', 'qty' => 30],
                ['name' => 'Mentega', 'qty' => 10],
                ['name' => 'Gula Pasir', 'qty' => 15],
                ['name' => 'Biskuit', 'qty' => 2],
            ],
            'Platter Breakfast' => [
                ['name' => 'Roti Gandum', 'qty' => 2],
                ['name' => 'Telur', 'qty' => 1],
                ['name' => 'Kopi Arabica', 'qty' => 8],
                ['name' => 'Susu UHT', 'qty' => 100],
            ],
            'Platter Snack' => [
                ['name' => 'Biskuit', 'qty' => 3],
                ['name' => 'Susu UHT', 'qty' => 50],
                ['name' => 'Cokelat Bubuk', 'qty' => 10],
            ],
        ];

        foreach ($recipes as $productName => $recipeItems) {
            $product = Product::where('name', $productName)->first();

            if (! $product) {
                continue;
            }

            foreach ($recipeItems as $item) {
                $ingredient = Ingredient::where('name', $item['name'])->first();

                if (! $ingredient) {
                    continue;
                }

                $product->ingredients()->syncWithoutDetaching([
                    $ingredient->id => ['qty' => (float) $item['qty']],
                ]);
            }
        }
    }
}
