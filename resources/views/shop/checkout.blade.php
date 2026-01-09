<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white border rounded-xl p-4">
                <div class="font-semibold text-lg mb-3">Data Pemesan</div>

                <form method="POST" action="{{ route('checkout.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="text-sm">Nama</label>
                        <input name="name" value="{{ old('name', auth()->user()->name) }}" class="w-full border rounded-lg px-3 py-2">
                        @error('name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">No. HP</label>
                        <input name="phone" value="{{ old('phone') }}" class="w-full border rounded-lg px-3 py-2">
                        @error('phone') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Alamat</label>
                        <textarea name="address" class="w-full border rounded-lg px-3 py-2" rows="3">{{ old('address') }}</textarea>
                        @error('address') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Metode Pembayaran</label>
                        <select name="payment_method" class="w-full border rounded-lg px-3 py-2">
                            <option value="cash">Cash</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                        @error('payment_method') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <button class="w-full px-4 py-2 bg-amber-500 text-white rounded-lg">
                        Buat Pesanan
                    </button>
                </form>
            </div>

            <div class="bg-white border rounded-xl p-4">
                <div class="font-semibold text-lg mb-3">Ringkasan</div>

                <div class="space-y-2">
                    @foreach ($cart as $item)
                        <div class="flex justify-between">
                            <div>{{ $item['name'] }} x{{ $item['qty'] }}</div>
                            <div>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t flex justify-between font-bold">
                    <div>Total</div>
                    <div>Rp {{ number_format($total, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
