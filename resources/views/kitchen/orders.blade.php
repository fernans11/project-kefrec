<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Board Dapur - KeFrec Coffee Shop</title>

    <script>
        (function () {
            try {
                document.documentElement.dataset.theme = localStorage.getItem('kefrec-theme') || 'dark';
            } catch (_) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/customer-landing.css') }}">
</head>
<body>
    <header class="navbar">
        <div class="container navbar-inner">
            <div class="logo-area">
                <div class="logo-box">
                    <img src="" alt="Logo KeFrec"
                         onerror="this.style.display='none'; this.parentElement.querySelector('.logo-fallback').style.display='block';">
                    <span class="logo-fallback" style="display:none;" data-lucide="coffee"></span>
                </div>
                <div class="brand-text">
                    <span class="brand-title">KeFrec CoffeeShop</span>
                </div>
            </div>

            <div class="right-area">
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" aria-pressed="false">
                    <span class="theme-toggle-icon" data-theme-icon data-lucide="moon"></span>
                    <span class="theme-toggle-text" data-theme-text>Dark</span>
                </button>
                <a href="{{ route('kitchen.ingredients.index') }}" class="btn btn-outline-light btn-small">Cek Stok</a>
                <a href="{{ route('employee.attendance') }}" class="btn btn-outline-light btn-small">Absensi</a>
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
                    <h3 class="card-title">Board Dapur</h3>
                    <p class="card-subtitle">Kelola pesanan yang sedang diproses dan siap diambil.</p>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div style="margin-bottom:0.8rem;padding:0.6rem 0.8rem;border-radius:10px;background:rgba(76,175,80,0.16);border:1px solid rgba(76,175,80,0.55);color:var(--green);font-weight:600;">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div style="margin-bottom:0.8rem;padding:0.6rem 0.8rem;border-radius:10px;background:rgba(244,67,54,0.16);border:1px solid rgba(244,67,54,0.55);color:var(--red-main);font-weight:600;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;">
                        <button class="btn btn-red btn-small" type="button" id="tab-orders">Pesanan</button>
                    </div>

                    <div id="tab-content-orders" style="display:flex;flex-direction:column;gap:1rem;">
                        @forelse ($orders as $order)
                            @php
                                $statusLabel = $order->status === 'ready' ? 'Siap Diambil' : 'Diproses';
                                $badgeColor = $order->status === 'ready'
                                    ? 'rgba(76,175,80,0.25)'
                                    : 'rgba(255,193,7,0.18)';
                                $badgeBorder = $order->status === 'ready'
                                    ? 'rgba(76,175,80,0.6)'
                                    : 'rgba(255,193,7,0.45)';
                            @endphp
                            <div style="border:1px solid var(--border-dark);border-radius:14px;background:var(--surface-subtle);padding:0.9rem;color:var(--text-main);">
                                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                                    <div>
                                        <div style="font-size:0.85rem;color:var(--text-muted);">Invoice</div>
                                        <div style="font-weight:600;">{{ $order->invoice_no }}</div>
                                        <div style="font-size:0.85rem;color:var(--text-muted);margin-top:0.2rem;">
                                            {{ $order->customer?->name ?? 'Customer' }}
                                        </div>
                                        <div style="font-size:0.8rem;color:var(--text-muted);">
                                            {{ $order->created_at->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="display:inline-flex;align-items:center;gap:0.3rem;border-radius:999px;padding:0.2rem 0.7rem;background:{{ $badgeColor }};border:1px solid {{ $badgeBorder }};font-size:0.78rem;color:var(--text-main);">
                                            {{ $statusLabel }}
                                        </div>
                                        <div style="margin-top:0.4rem;font-weight:700;color:var(--red-main);">
                                            Rp {{ number_format($order->total, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                <div style="margin-top:0.7rem;padding-top:0.7rem;border-top:1px solid var(--border-dark);">
                                    <div style="font-weight:600;margin-bottom:0.5rem;">Item Pesanan</div>
                                    <div style="display:flex;flex-direction:column;gap:0.5rem;">
                                        @foreach ($order->items as $it)
                                            <div style="display:flex;align-items:center;justify-content:space-between;background:var(--bg-muted);border:1px solid var(--border-dark);border-radius:10px;padding:0.5rem 0.7rem;color:var(--text-main);">
                                                <div style="font-size:0.85rem;">{{ $it->product?->name }} x{{ $it->qty }}</div>
                                                <div style="font-weight:600;">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div style="margin-top:0.8rem;display:flex;justify-content:flex-end;gap:0.5rem;flex-wrap:wrap;">
                                    @if ($order->status === 'processing')
                                        <form method="POST" action="{{ route('kitchen.orders.ready', $order) }}">
                                            @csrf
                                            <button class="btn btn-outline-light" type="submit">
                                                Tandai Siap
                                            </button>
                                        </form>
                                    @endif
                                    @if ($order->status === 'ready')
                                        <form method="POST" action="{{ route('kitchen.orders.completed', $order) }}">
                                            @csrf
                                            <button class="btn btn-red" type="submit">
                                                Selesaikan Pesanan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="padding:1rem;border-radius:12px;border:1px dashed var(--border-dark);color:var(--text-muted);text-align:center;">
                                Tidak ada pesanan di dapur.
                            </div>
                        @endforelse
                    </div>

                    <div style="margin-top:1rem;">
                        {{ $orders->links() }}
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script src="{{ asset('js/lucide-icons.js') }}"></script>
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
</body>
</html>
