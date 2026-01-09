<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function cart(): array
    {
        return session()->get('cart', []);
    }

    private function save(array $cart): void
    {
        session()->put('cart', $cart);
    }

    public function index()
    {
        $cart = $this->cart();
        $total = collect($cart)->sum(fn ($i) => $i['subtotal']);

        return view('shop.cart', compact('cart', 'total'));
    }

    public function add(Product $product, Request $request)
    {
        $qty = max(1, (int) $request->input('qty', 1));

        $cart = $this->cart();
        $id = (string) $product->id;

        if (!isset($cart[$id])) {
            $cart[$id] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => (int) $product->price,
                'qty'        => 0,
                'subtotal'   => 0,
            ];
        }

        $cart[$id]['qty'] += $qty;
        $cart[$id]['subtotal'] = $cart[$id]['qty'] * $cart[$id]['price'];

        $this->save($cart);

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request)
    {
        $items = $request->input('items', []);
        $cart = $this->cart();

        foreach ($items as $productId => $qty) {
            $qty = (int) $qty;

            if (!isset($cart[$productId])) continue;

            if ($qty <= 0) {
                unset($cart[$productId]);
                continue;
            }

            $cart[$productId]['qty'] = $qty;
            $cart[$productId]['subtotal'] = $qty * $cart[$productId]['price'];
        }

        $this->save($cart);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function remove(string $productId)
    {
        $cart = $this->cart();
        unset($cart[$productId]);
        $this->save($cart);

        return back()->with('success', 'Item dihapus.');
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Keranjang dikosongkan.');
    }
}
