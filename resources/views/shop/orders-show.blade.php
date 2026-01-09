<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pesanan</h2>
            <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4">
            <div class="bg-white border rounded-xl p-4">
                <div class="flex justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Invoice</div>
                        <div class="font-semibold">{{ $transaction->invoice_no }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="font-semibold">{{ ucfirst($transaction->status) }}</div>
                    </div>
                </div>

                <div class="mt-4 border-t pt-4">
                    <div class="font-semibold mb-2">Items</div>
                    @foreach ($transaction->items as $it)
                        <div class="flex justify-between">
                            <div>{{ $it->product?->name }} x{{ $it->qty }}</div>
                            <div>Rp {{ number_format($it->subtotal, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 border-t pt-4 flex justify-between font-bold">
                    <div>Total</div>
                    <div>Rp {{ number_format($transaction->total, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
