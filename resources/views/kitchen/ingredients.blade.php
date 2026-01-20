<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stok Bahan Baku - KeFrec Coffee Shop</title>

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
                <a href="{{ route('kitchen.orders.index') }}" class="btn btn-outline-light btn-small">Board Dapur</a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-red btn-small" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="page">
        <div class="container">
            <section class="card">
                <div class="card-header">
                    <h3 class="card-title">Stok Bahan Baku</h3>
                    <p class="card-subtitle">Pantau ketersediaan bahan baku untuk produksi.</p>
                </div>
                <div class="card-body">
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        @forelse ($ingredients as $ingredient)
                            @php
                                $isLow = (float) $ingredient->stock <= (float) $ingredient->min_stock;
                            @endphp
                            <div style="border:1px solid var(--border-dark);border-radius:12px;background:rgba(58,58,58,0.25);padding:0.8rem;">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;">
                                    <div>
                                        <div style="font-weight:600;">{{ $ingredient->name }}</div>
                                        <div style="font-size:0.8rem;color:var(--text-muted);">
                                            Min stok: {{ rtrim(rtrim(number_format($ingredient->min_stock, 2, ',', '.'), '0'), ',') }} {{ $ingredient->unit }}
                                        </div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-size:0.8rem;color:var(--text-muted);">Stok Saat Ini</div>
                                        <div style="font-weight:700;color:{{ $isLow ? '#ffb3b3' : '#dffbe3' }};">
                                            {{ rtrim(rtrim(number_format($ingredient->stock, 2, ',', '.'), '0'), ',') }} {{ $ingredient->unit }}
                                        </div>
                                    </div>
                                </div>
                                @if ($isLow)
                                    <div style="margin-top:0.5rem;padding:0.4rem 0.6rem;border-radius:10px;border:1px solid rgba(244,67,54,0.6);background:rgba(244,67,54,0.18);font-size:0.78rem;color:#ffd6d6;">
                                        Stok menipis, segera lakukan pembelian bahan baku.
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div style="padding:1rem;border-radius:12px;border:1px dashed var(--border-dark);color:var(--text-muted);text-align:center;">
                                Belum ada data bahan baku.
                            </div>
                        @endforelse
                    </div>

                    <div style="margin-top:1rem;">
                        {{ $ingredients->links() }}
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
