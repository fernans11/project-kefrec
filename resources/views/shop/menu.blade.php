<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Menu KeFrec</h2>
            <a href="{{ route('cart.index') }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg">
                Keranjang
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <form method="GET" class="mb-6 flex gap-2">
                <input name="q" value="{{ $q }}" class="w-full border rounded-lg px-3 py-2" placeholder="Cari menu...">
                <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">Cari</button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($products as $p)
                    <div class="border rounded-xl p-4 bg-white shadow-sm">
                        <div class="text-sm text-gray-500">{{ $p->category }}</div>
                        <div class="text-lg font-semibold">{{ $p->name }}</div>
                        <div class="mt-2 text-gray-700">{{ $p->description }}</div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="font-bold">Rp {{ number_format($p->price, 0, ',', '.') }}</div>

                            <form method="POST" action="{{ route('cart.add', $p) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="number" name="qty" value="1" min="1" class="w-16 border rounded px-2 py-1">
                                <button class="px-3 py-2 bg-amber-500 text-white rounded-lg">Tambah</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
