<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Support\BestSellerUpdater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BestSellerUpdaterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_two_best_sellers_for_each_menu_category(): void
    {
        $productsByCategory = collect(BestSellerUpdater::CATEGORIES)
            ->mapWithKeys(fn (string $category) => [
                $category => collect([10, 30, 20])->map(
                    fn (int $qty, int $index) => $this->productWithCompletedSale($category, $index, $qty)
                ),
            ]);

        BestSellerUpdater::sync();

        foreach ($productsByCategory as $category => $products) {
            $popularProducts = Product::query()
                ->where('category', $category)
                ->where('is_popular', true)
                ->orderBy('name')
                ->pluck('name')
                ->all();

            $this->assertSame([
                "{$category} 2",
                "{$category} 3",
            ], $popularProducts);
        }

        $this->assertSame(6, Product::query()->where('is_popular', true)->count());
    }

    public function test_it_clears_best_seller_when_products_have_never_been_ordered(): void
    {
        foreach (BestSellerUpdater::CATEGORIES as $category) {
            $this->product($category, 0);
        }

        BestSellerUpdater::sync();

        $this->assertSame(0, Product::query()->where('is_popular', true)->count());
    }

    public function test_new_product_label_is_added_automatically_only_for_newly_created_products(): void
    {
        $newProduct = Product::create([
            'name' => 'Seasonal Matcha',
            'slug' => 'seasonal-matcha',
            'category' => 'Minuman',
            'price' => 28000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $existingSeedProduct = Product::create([
            'name' => 'Seed Cappuccino',
            'slug' => 'seed-cappuccino',
            'category' => 'Minuman',
            'price' => 25000,
            'is_active' => true,
            'is_new' => false,
            'sort_order' => 2,
        ]);

        $this->assertTrue($newProduct->refresh()->is_new);
        $this->assertFalse($existingSeedProduct->refresh()->is_new);
    }

    private function product(string $category, int $index): Product
    {
        return Product::create([
            'name' => "{$category} " . ($index + 1),
            'slug' => strtolower($category) . '-' . ($index + 1),
            'category' => $category,
            'price' => 10000,
            'is_active' => true,
            'is_popular' => true,
            'sort_order' => $index + 1,
        ]);
    }

    private function productWithCompletedSale(string $category, int $index, int $qty): Product
    {
        $product = $this->product($category, $index);

        $this->completedSale($product, $qty);

        return $product;
    }

    private function completedSale(Product $product, int $qty): void
    {
        $transaction = Transaction::withoutEvents(fn () => Transaction::create([
            'invoice_no' => 'INV-' . $product->id,
            'subtotal' => $qty * 10000,
            'discount' => 0,
            'tax' => 0,
            'total' => $qty * 10000,
            'status' => 'completed',
        ]));

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'qty' => $qty,
            'price' => 10000,
            'subtotal' => $qty * 10000,
        ]);
    }
}
