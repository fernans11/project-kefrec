<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Persetujuan Kasir - KeFrec Coffee Shop</title>

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
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-small">Beranda</a>
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
                    <h3 class="card-title">Persetujuan Kasir</h3>
                    <p class="card-subtitle">Pesanan menunggu persetujuan sebelum diproses dapur.</p>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div style="margin-bottom:0.8rem;padding:0.6rem 0.8rem;border-radius:10px;background:rgba(76,175,80,0.2);border:1px solid rgba(76,175,80,0.55);color:#dffbe3;">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div style="margin-bottom:0.8rem;padding:0.6rem 0.8rem;border-radius:10px;background:rgba(244,67,54,0.2);border:1px solid rgba(244,67,54,0.55);color:#ffd6d6;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;">
                        <button class="btn btn-red btn-small" type="button" id="tab-orders">Pesanan</button>
                        <button class="btn btn-outline-light btn-small" type="button" id="tab-attendance">Absensi</button>
                    </div>

                    <div id="tab-content-orders" style="display:flex;flex-direction:column;gap:1rem;">
                        @forelse ($orders as $order)
                            <div style="border:1px solid var(--border-dark);border-radius:14px;background:rgba(58,58,58,0.25);padding:0.9rem;">
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
                                        <div style="font-size:0.85rem;color:var(--text-muted);">Total</div>
                                        <div style="font-weight:700;color:var(--red-main);">
                                            Rp {{ number_format($order->total, 0, ',', '.') }}
                                        </div>
                                        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.2rem;">
                                            Metode: {{ strtoupper($order->payment_method ?? '-') }}
                                        </div>
                                    </div>
                                </div>

                                <div style="margin-top:0.7rem;padding-top:0.7rem;border-top:1px solid var(--border-dark);">
                                    <div style="font-weight:600;margin-bottom:0.5rem;">Item Pesanan</div>
                                    <div style="display:flex;flex-direction:column;gap:0.5rem;">
                                        @foreach ($order->items as $it)
                                            <div style="display:flex;align-items:center;justify-content:space-between;background:#1f1f1f;border:1px solid var(--border-dark);border-radius:10px;padding:0.5rem 0.7rem;">
                                                <div style="font-size:0.85rem;">{{ $it->product?->name }} x{{ $it->qty }}</div>
                                                <div style="font-weight:600;">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div style="margin-top:0.8rem;display:flex;justify-content:flex-end;">
                                    <form method="POST" action="{{ route('cashier.orders.approve', $order) }}">
                                        @csrf
                                        <button class="btn btn-red" type="submit">
                                            Setujui & Kirim ke Dapur
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div style="padding:1rem;border-radius:12px;border:1px dashed var(--border-dark);color:var(--text-muted);text-align:center;">
                                Tidak ada pesanan yang menunggu persetujuan.
                            </div>
                        @endforelse
                    </div>

                    <div id="tab-content-attendance" style="display:none;">
                        <div style="padding:0.9rem;border-radius:12px;border:1px solid var(--border-dark);background:#2a2222;">
                            <div style="font-weight:600;margin-bottom:0.4rem;">Absensi Kasir</div>
                            <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.6rem;">
                                Pilih nama Anda untuk check-in atau check-out.
                            </div>
                            <form method="POST" action="{{ route('attendance.check-in') }}">
                                @csrf
                                <select id="staff_id_cashier" name="staff_id" required
                                    style="width:100%;padding:0.45rem 0.6rem;border-radius:10px;border:1px solid var(--border-dark);background:#1f1f1f;color:#fff;font-size:0.85rem;">
                                    <option value="">Pilih staff</option>
                                    @foreach ($staffMembers as $person)
                                        <option value="{{ $person->id }}">{{ $person->name }}{{ $person->position ? ' - ' . $person->position : '' }}</option>
                                    @endforeach
                                </select>
                                <div style="margin-top:0.6rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                                    <button class="btn btn-outline-light btn-small" type="submit">Check-in</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('attendance.check-out') }}" style="margin-top:0.6rem;">
                                @csrf
                                <input type="hidden" name="staff_id" id="staff_id_cashier_out">
                                <button class="btn btn-red btn-small" type="submit">Check-out</button>
                            </form>
                        </div>
                    </div>

                    <div style="margin-top:1rem;">
                        {{ $orders->links() }}
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script>
        const staffCashier = document.getElementById('staff_id_cashier');
        const staffCashierOut = document.getElementById('staff_id_cashier_out');
        if (staffCashier && staffCashierOut) {
            staffCashier.addEventListener('change', () => {
                staffCashierOut.value = staffCashier.value;
            });
        }

        const tabOrders = document.getElementById('tab-orders');
        const tabAttendance = document.getElementById('tab-attendance');
        const contentOrders = document.getElementById('tab-content-orders');
        const contentAttendance = document.getElementById('tab-content-attendance');

        function activateTab(active) {
            const isOrders = active === 'orders';
            if (contentOrders) contentOrders.style.display = isOrders ? 'flex' : 'none';
            if (contentAttendance) contentAttendance.style.display = isOrders ? 'none' : 'block';
            if (tabOrders) tabOrders.className = isOrders ? 'btn btn-red btn-small' : 'btn btn-outline-light btn-small';
            if (tabAttendance) tabAttendance.className = isOrders ? 'btn btn-outline-light btn-small' : 'btn btn-red btn-small';
        }

        if (tabOrders && tabAttendance) {
            tabOrders.addEventListener('click', () => activateTab('orders'));
            tabAttendance.addEventListener('click', () => activateTab('attendance'));
        }
    </script>
</body>
</html>
