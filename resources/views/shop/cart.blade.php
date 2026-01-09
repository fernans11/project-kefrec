<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Keranjang</h2>
            <a href="{{ route('menu.index') }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg">
                Kembali ke Menu
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            @if (empty($cart))
                <div class="p-6 bg-white rounded-xl border">
                    Keranjang kosong.
                </div>
            @else
                <form method="POST" action="{{ route('cart.update') }}" class="bg-white rounded-xl border p-4">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Produk</th>
                                    <th class="py-2">Harga</th>
                                    <th class="py-2 w-28">Qty</th>
                                    <th class="py-2">Subtotal</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cart as $id => $item)
                                    <tr class="border-b">
                                        <td class="py-3">{{ $item['name'] }}</td>
                                        <td class="py-3">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td class="py-3">
                                            <input type="number" name="items[{{ $id }}]" value="{{ $item['qty'] }}" min="0" class="w-24 border rounded px-2 py-1">
                                        </td>
                                        <td class="py-3">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                        <td class="py-3">
                                            <form method="POST" action="{{ route('cart.remove', $id) }}">
                                                @csrf
                                                <button class="text-red-600">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <div class="font-bold text-lg">Total: Rp {{ number_format($total, 0, ',', '.') }}</div>

                        <div class="flex gap-2">
                            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">Update</button>

                            <a href="{{ route('checkout.index') }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg">
                                Checkout
                            </a>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
