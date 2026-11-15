<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pesanan Berjalan - KeFrec Coffee Shop</title>

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
                    <span class="logo-fallback" style="display:none;">KF</span>
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
                <a href="{{ route('member.show') }}" class="btn btn-outline-light btn-small">Membership</a>
                <a href="{{ route('home') }}" class="btn btn-red btn-small">Beranda</a>
            </div>
        </div>
    </header>

    <main class="page">
        <div class="container">
            <section class="card card-highlight" style="padding:1.2rem;margin-bottom:1rem;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:0.9rem;color:var(--text-muted);">Pesanan</div>
                        <h1 style="font-size:1.7rem;font-weight:800;margin-top:0.15rem;">Pesanan Sedang Berjalan</h1>
                        <div style="font-size:0.9rem;color:var(--text-muted);margin-top:0.2rem;">
                            Pantau pesanan yang masih menunggu pembayaran, kasir, atau dapur.
                        </div>
                    </div>
                    <a href="{{ route('home') }}#menu-kami" class="btn btn-outline-light">Pesan Lagi</a>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Pesanan</h2>
                    <p class="card-subtitle">Status akan berubah setelah pembayaran, persetujuan kasir, dan proses dapur.</p>
                </div>
                <div class="card-body">
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        @forelse ($activeOrders as $transaction)
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;border:1px solid var(--border-dark);background:var(--surface-subtle);border-radius:12px;padding:0.85rem;flex-wrap:wrap;">
                                <div>
                                    <div style="font-weight:800;">{{ $transaction->invoice_no }}</div>
                                    <div style="font-size:0.84rem;color:var(--text-muted);margin-top:0.12rem;">
                                        {{ $transaction->created_at->format('d M Y H:i') }} - {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:0.65rem;flex-wrap:wrap;">
                                    <strong>Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong>
                                    <a href="{{ route('orders.show', $transaction) }}" class="btn btn-red btn-small">Detail</a>
                                </div>
                            </div>
                        @empty
                            <div style="padding:1rem;border-radius:12px;border:1px dashed var(--border-dark);color:var(--text-muted);text-align:center;">
                                Tidak ada pesanan yang sedang berjalan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="{{ asset('js/lucide-icons.js') }}"></script>
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
</body>
</html>
