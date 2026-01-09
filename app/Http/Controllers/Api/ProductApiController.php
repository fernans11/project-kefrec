<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category'); // Minuman | Makanan | Platters

        $query = Product::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($category && $category !== 'Semua') {
            $query->where('category', $category);
        }

        return response()->json([
            'data' => $query->get([
                'id',
                'name',
                'slug',
                'category',
                'description',
                'price',
                'image_url',
                'is_popular',
                'is_new',
                'rating',
            ]),
        ]);
    }
}
