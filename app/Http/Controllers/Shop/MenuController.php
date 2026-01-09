<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $products = Product::query()
            ->where('is_active', true)
            ->when($q, fn ($qr) => $qr->where('name', 'like', "%{$q}%"))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('shop.menu', compact('products', 'q'));
    }
}
