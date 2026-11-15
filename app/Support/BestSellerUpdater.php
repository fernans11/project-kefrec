<?php

namespace App\Support;

use App\Models\Product;
use App\Models\TransactionItem;

class BestSellerUpdater
{
    public const LIMIT_PER_CATEGORY = 2;

    public const CATEGORIES = [
        'Minuman',
        'Makanan',
        'Platters',
    ];

    public static function sync(): void
    {
        $bestSellerIds = collect(self::CATEGORIES)
            ->flatMap(fn (string $category) => self::bestSellerIdsForCategory($category))
            ->unique()
            ->values()
            ->all();

        Product::query()->update(['is_popular' => false]);

        if ($bestSellerIds !== []) {
            Product::query()
                ->whereIn('id', $bestSellerIds)
                ->update(['is_popular' => true]);
        }
    }

    private static function bestSellerIdsForCategory(string $category): array
    {
        return TransactionItem::query()
            ->select('transaction_items.product_id')
            ->selectRaw('SUM(transaction_items.qty) as ordered_qty')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->where('transactions.status', 'completed')
            ->where('products.is_active', true)
            ->where('products.category', $category)
            ->whereNotNull('transaction_items.product_id')
            ->groupBy('transaction_items.product_id', 'products.sort_order', 'products.id')
            ->havingRaw('SUM(transaction_items.qty) > 0')
            ->orderByDesc('ordered_qty')
            ->orderBy('products.sort_order')
            ->orderBy('products.id')
            ->limit(self::LIMIT_PER_CATEGORY)
            ->pluck('transaction_items.product_id')
            ->all();
    }
}
