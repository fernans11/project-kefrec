<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pesanan Saya</h2>
            <a href="{{ route('menu.index') }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg">Pesan Lagi</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4">
            <div class="bg-white border rounded-xl overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 px-4">Invoice</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $o)
                            <tr class="border-b">
                                <td class="py-3 px-4">{{ $o->invoice_no }}</td>
                                <td class="py-3 px-4">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                                <td class="py-3 px-4">{{ ucfirst($o->status) }}</td>
                                <td class="py-3 px-4">{{ $o->created_at }}</td>
                                <td class="py-3 px-4">
                                    <a class="text-amber-600" href="{{ route('orders.show', $o) }}">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 px-4 text-gray-500">Belum ada pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
