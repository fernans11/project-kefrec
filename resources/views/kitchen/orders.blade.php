<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Board Dapur - KeFrec Coffee Shop</title>

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
                <a href="{{ route('kitchen.ingredients.index') }}" class="btn btn-outline-light btn-small">Cek Stok</a>
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
                            @php
                                $statusLabel = $order->status === 'ready' ? 'Siap Diambil' : 'Diproses';
                                $badgeColor = $order->status === 'ready'
                                    ? 'rgba(76,175,80,0.25)'
                                    : 'rgba(255,193,7,0.18)';
                                $badgeBorder = $order->status === 'ready'
                                    ? 'rgba(76,175,80,0.6)'
                                    : 'rgba(255,193,7,0.45)';
                            @endphp
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
                                        <div style="display:inline-flex;align-items:center;gap:0.3rem;border-radius:999px;padding:0.2rem 0.7rem;background:{{ $badgeColor }};border:1px solid {{ $badgeBorder }};font-size:0.78rem;">
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
                                            <div style="display:flex;align-items:center;justify-content:space-between;background:#1f1f1f;border:1px solid var(--border-dark);border-radius:10px;padding:0.5rem 0.7rem;">
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

                    <div id="tab-content-attendance" style="display:none;">
                        <div style="padding:0.9rem;border-radius:12px;border:1px solid var(--border-dark);background:#2a2222;">
                            <div style="font-weight:600;margin-bottom:0.4rem;">Absensi Dapur</div>
                            <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.6rem;">
                                Pilih nama Anda untuk check-in atau check-out.
                            </div>
                            <form method="POST" action="{{ route('attendance.check-in') }}">
                                @csrf
                                <select id="staff_id_kitchen" name="staff_id" required
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
                                <input type="hidden" name="staff_id" id="staff_id_kitchen_out">
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
        const staffKitchen = document.getElementById('staff_id_kitchen');
        const staffKitchenOut = document.getElementById('staff_id_kitchen_out');
        if (staffKitchen && staffKitchenOut) {
            staffKitchen.addEventListener('change', () => {
                staffKitchenOut.value = staffKitchen.value;
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
