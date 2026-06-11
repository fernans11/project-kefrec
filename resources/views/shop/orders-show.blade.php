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
                @php
                    $status = $transaction->status;
                    if ($status === 'paid') {
                        $status = 'pending_cashier';
                    }
                    $steps = [
                        'pending_payment' => 'Menunggu Pembayaran',
                        'pending_cashier' => 'Menunggu Kasir',
                        'processing' => 'Diproses Dapur',
                        'ready' => 'Siap Diambil',
                        'completed' => 'Selesai',
                    ];
                    $currentIndex = array_search($status, array_keys($steps), true);
                @endphp

                <div class="flex justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Invoice</div>
                        <div class="font-semibold">{{ $transaction->invoice_no }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="font-semibold">{{ $steps[$status] ?? ucfirst($transaction->status) }}</div>
                    </div>
                </div>

                <div class="mt-4 border-t pt-4">
                    <div class="font-semibold mb-3">Progress Pesanan</div>
                    @if ($transaction->status === 'cancelled')
                        <div class="text-red-600 font-semibold">Pesanan dibatalkan.</div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            @foreach ($steps as $key => $label)
                                @php
                                    $idx = array_search($key, array_keys($steps), true);
                                    $isActive = $currentIndex !== false && $idx <= $currentIndex;
                                @endphp
                                <div class="border rounded-lg p-3 {{ $isActive ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200' }}">
                                    <div class="text-sm {{ $isActive ? 'text-emerald-700' : 'text-gray-500' }}">
                                        {{ $label }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($status === 'pending_payment')
                            <div class="mt-2 text-sm text-gray-600">
                                Menunggu pembayaran Anda dikonfirmasi oleh Midtrans.
                            </div>
                        @elseif ($status === 'pending_cashier')
                            <div class="mt-2 text-sm text-gray-600">
                                Menunggu kasir menyetujui pesanan Anda.
                            </div>
                        @endif
                    @endif
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
