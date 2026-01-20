<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Pesanan - KeFrec Coffee Shop</title>

    <link rel="stylesheet" href="{{ asset('css/customer-landing.css') }}">
</head>
<body>
    <header class="navbar">
        <div class="container navbar-inner">
            <div class="logo-area">
                <div class="logo-box">
                    <img src="" alt="Logo KeFrec"
                         onerror="this.style.display='none'; this.parentElement.querySelector('.logo-fallback').style.display='block';">
                    <span class="logo-fallback" style="display:none;">KF</span>
                </div>
                <div class="brand-text">
                    <span class="brand-title">KeFrec CoffeeShop</span>
                </div>
            </div>

            <div class="right-area">
                <a href="{{ route('orders.index') }}" class="btn btn-outline-light btn-small">Kembali</a>
                <a href="{{ route('home') }}" class="btn btn-red btn-small">Beranda</a>
            </div>
        </div>
    </header>

    <main class="page">
        <div class="container">
            @php
                $status = $transaction->status;
                if ($status === 'paid') {
                    $status = 'pending_cashier';
                }
                $steps = [
                    'pending_cashier' => 'Menunggu Kasir',
                    'processing' => 'Diproses Dapur',
                    'ready' => 'Siap Diambil',
                    'completed' => 'Selesai',
                ];
                $currentIndex = array_search($status, array_keys($steps), true);
            @endphp

            <section class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Pesanan</h3>
                    <p class="card-subtitle">Status dan ringkasan pesanan Anda.</p>
                </div>
                <div class="card-body">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
                        <div>
                            <div style="font-size:0.85rem;color:var(--text-muted);">Invoice</div>
                            <div style="font-weight:600;">{{ $transaction->invoice_no }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.85rem;color:var(--text-muted);">Status</div>
                            <div style="font-weight:600;">
                                {{ $steps[$status] ?? ucfirst($transaction->status) }}
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border-dark);">
                        <div style="font-weight:600;margin-bottom:0.6rem;">Progress Pesanan</div>
                        @if ($transaction->status === 'cancelled')
                            <div style="color:#ff5252;font-weight:600;">Pesanan dibatalkan.</div>
                        @else
                            <div style="display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:0.7rem;">
                                @foreach ($steps as $key => $label)
                                    @php
                                        $idx = array_search($key, array_keys($steps), true);
                                        $isActive = $currentIndex !== false && $idx <= $currentIndex;
                                    @endphp
                                    <div style="border-radius:12px;border:1px solid {{ $isActive ? 'rgba(76,175,80,0.6)' : 'var(--border-dark)' }};background:{{ $isActive ? 'rgba(76,175,80,0.12)' : 'rgba(58,58,58,0.25)' }};padding:0.7rem 0.8rem;">
                                        <div style="font-size:0.85rem;{{ $isActive ? 'color:#dffbe3;' : 'color:var(--text-muted);' }}">
                                            {{ $label }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($status === 'pending_cashier')
                                <div style="margin-top:0.5rem;font-size:0.85rem;color:var(--text-muted);">
                                    Menunggu kasir menyetujui pesanan Anda.
                                </div>
                            @endif
                        @endif
                    </div>

                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border-dark);">
                        <div style="font-weight:600;margin-bottom:0.6rem;">Item Pesanan</div>
                        <div style="display:flex;flex-direction:column;gap:0.6rem;">
                            @foreach ($transaction->items as $it)
                                <div style="display:flex;align-items:center;justify-content:space-between;background:rgba(58,58,58,0.25);border:1px solid var(--border-dark);border-radius:10px;padding:0.6rem 0.7rem;">
                                    <div style="font-size:0.9rem;">{{ $it->product?->name }} x{{ $it->qty }}</div>
                                    <div style="font-weight:600;">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border-dark);display:flex;align-items:center;justify-content:space-between;font-weight:700;">
                        <div>Total</div>
                        <div>Rp {{ number_format($transaction->total, 0, ',', '.') }}</div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
