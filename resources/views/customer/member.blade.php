<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Membership - KeFrec Coffee Shop</title>

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
    <style>
        .membership-title-row {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            position: relative;
        }

        .membership-help-button {
            width: 1.65rem;
            height: 1.65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid var(--border-dark);
            background: var(--bg-elevated);
            color: var(--red-main);
            cursor: pointer;
            padding: 0;
        }

        .membership-help-button:hover,
        .membership-help-button:focus-visible {
            background: var(--bg-hover);
            outline: none;
        }

        .membership-rules-popover {
            position: absolute;
            top: calc(100% + 0.55rem);
            left: 0;
            z-index: 20;
            width: min(24rem, calc(100vw - 2rem));
            padding: 0.85rem;
            border-radius: 12px;
            border: 1px solid var(--border-dark);
            background: var(--bg-card);
            color: var(--text-main);
            box-shadow: var(--shadow-soft);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-4px);
            transition: opacity 0.16s ease, transform 0.16s ease, visibility 0.16s ease;
        }

        .membership-title-row:hover .membership-rules-popover,
        .membership-title-row:focus-within .membership-rules-popover,
        .membership-rules-popover.is-open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .membership-rules-title {
            font-weight: 800;
            margin-bottom: 0.45rem;
        }

        .membership-rules-list {
            margin: 0;
            padding-left: 1rem;
            color: var(--text-muted);
            font-size: 0.86rem;
            line-height: 1.55;
        }

        .membership-rules-list strong {
            color: var(--text-main);
        }

        @media (max-width: 640px) {
            .membership-rules-popover {
                left: 50%;
                transform: translate(-50%, -4px);
            }

            .membership-title-row:hover .membership-rules-popover,
            .membership-title-row:focus-within .membership-rules-popover,
            .membership-rules-popover.is-open {
                transform: translate(-50%, 0);
            }
        }
    </style>
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
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-small">Beranda</a>
                <a href="{{ route('member.orders.active') }}" class="btn btn-red btn-small">Pesanan</a>
            </div>
        </div>
    </header>

    @php
        $points = (int) ($stats['points'] ?? 0);
        $totalSpent = (int) ($stats['total_spent'] ?? 0);
        $status = $stats['status'] ?? 'Bronze';
        $tiers = [
            ['name' => 'Bronze', 'min' => 0],
            ['name' => 'Silver', 'min' => 50],
            ['name' => 'Gold', 'min' => 100],
            ['name' => 'Platinum', 'min' => 150],
            ['name' => 'Honorable Member', 'min' => 200],
        ];
        $nextTier = collect($tiers)->first(fn ($tier) => $tier['min'] > $points);
        $currentTierMin = collect($tiers)->filter(fn ($tier) => $tier['min'] <= $points)->last()['min'] ?? 0;
        $nextTierMin = $nextTier['min'] ?? max(200, $points);
        $progressMax = max(1, $nextTierMin - $currentTierMin);
        $progressValue = $nextTier ? min(100, (($points - $currentTierMin) / $progressMax) * 100) : 100;
        $displayUsername = $user->username ?: $user->name;
        $displayFullName = $customer?->name ?: $user->name;
    @endphp

    <main class="page">
        <div class="container">
            <section class="card card-highlight" style="padding:1.2rem;margin-bottom:1rem;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <div class="membership-title-row">
                            <div style="font-size:0.9rem;color:var(--text-muted);">Membership</div>
                            <button class="membership-help-button" type="button" id="membership-rules-toggle" aria-label="Lihat aturan poin membership" aria-expanded="false" aria-controls="membership-rules-popover">
                                <span data-lucide="circle-help"></span>
                            </button>
                            <div class="membership-rules-popover" id="membership-rules-popover" role="dialog" aria-label="Aturan poin membership">
                                <div class="membership-rules-title">Cara Mendapatkan Poin</div>
                                <ul class="membership-rules-list">
                                    <li>Transaksi harus berstatus <strong>completed</strong> agar poin dihitung.</li>
                                    <li>Total belanja di bawah Rp 100.000 mendapat <strong>1 poin</strong>.</li>
                                    <li>Total belanja Rp 100.000 sampai di bawah Rp 200.000 mendapat <strong>2 poin</strong>.</li>
                                    <li>Total belanja Rp 200.000 atau lebih mendapat <strong>5 poin</strong>.</li>
                                    <li>Level naik otomatis: Bronze, Silver 50 poin, Gold 100 poin, Platinum 150 poin, Honorable Member 200 poin.</li>
                                </ul>
                            </div>
                        </div>
                        <h1 style="font-size:1.7rem;font-weight:800;margin-top:0.15rem;">{{ $displayUsername }}</h1>
                        <div style="font-size:0.9rem;color:var(--text-muted);margin-top:0.2rem;">{{ $displayFullName }}</div>
                    </div>
                    <span class="member-badge">{{ $status }}</span>
                </div>

                <div class="benefit-grid" style="margin-top:1rem;">
                    <div class="benefit-card">
                        <div class="benefit-icon" data-lucide="heart"></div>
                        <div class="benefit-title">Poin Terkumpul</div>
                        <div class="benefit-desc"><strong>{{ number_format($points, 0, ',', '.') }}</strong> poin</div>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon" data-lucide="trending-up"></div>
                        <div class="benefit-title">Total Belanja</div>
                        <div class="benefit-desc"><strong>Rp {{ number_format($totalSpent, 0, ',', '.') }}</strong></div>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon" data-lucide="star"></div>
                        <div class="benefit-title">Target Berikutnya</div>
                        <div class="benefit-desc">
                            @if ($nextTier)
                                {{ max(0, $nextTier['min'] - $points) }} poin menuju {{ $nextTier['name'] }}
                            @else
                                Level membership tertinggi
                            @endif
                        </div>
                    </div>
                </div>

                <div style="margin-top:1rem;">
                    <div style="height:10px;border-radius:999px;background:var(--bg-dark);border:1px solid var(--border-dark);overflow:hidden;">
                        <div style="height:100%;width:{{ $progressValue }}%;background:var(--red-main);"></div>
                    </div>
                </div>
            </section>

            @if (session('success'))
                <div style="margin-bottom:1rem;padding:0.75rem 0.9rem;border-radius:12px;background:rgba(76,175,80,0.16);border:1px solid rgba(76,175,80,0.55);">
                    {{ session('success') }}
                </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr;gap:1rem;">
                <section class="card">
                    <div class="card-header">
                        <h2 class="card-title">Kirim Keluhan</h2>
                        <p class="card-subtitle">Keluhan akan masuk ke menu Komplain di admin panel.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('member.complaints.store') }}">
                            @csrf
                            <div class="checkout-form-group">
                                <label class="checkout-label" for="title">Judul Keluhan</label>
                                <input class="checkout-input" id="title" name="title" type="text" required maxlength="150" placeholder="Contoh: Pesanan terlambat" value="{{ old('title') }}">
                                @error('title')
                                    <div style="font-size:0.82rem;color:#ff6b6b;margin-top:0.25rem;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="checkout-form-group">
                                <label class="checkout-label" for="message">Isi Keluhan</label>
                                <textarea class="checkout-input" id="message" name="message" rows="5" required placeholder="Ceritakan kendala atau masukan Anda...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div style="font-size:0.82rem;color:#ff6b6b;margin-top:0.25rem;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="checkout-actions">
                                <button class="btn btn-red" type="submit">Kirim Keluhan</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="card">
                    <div class="card-header">
                        <h2 class="card-title">Riwayat Keluhan</h2>
                        <p class="card-subtitle">Status keluhan terbaru Anda.</p>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;flex-direction:column;gap:0.65rem;">
                            @forelse ($complaints as $complaint)
                                <div style="border:1px solid var(--border-dark);background:var(--surface-subtle);border-radius:12px;padding:0.75rem;">
                                    <div style="display:flex;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;">
                                        <strong>{{ $complaint->title ?: 'Keluhan' }}</strong>
                                        <span style="font-size:0.82rem;color:var(--text-muted);">{{ $complaint->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                    <div style="margin-top:0.25rem;font-size:0.84rem;color:var(--text-muted);">Status: {{ ucfirst($complaint->status) }}</div>
                                    <div style="margin-top:0.35rem;color:var(--text-muted);">{{ $complaint->message }}</div>
                                    @if ($complaint->response)
                                        <div style="margin-top:0.5rem;padding-top:0.5rem;border-top:1px solid var(--border-dark);">
                                            <strong>Tindak lanjut:</strong> {{ $complaint->response }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div style="color:var(--text-muted);">Belum ada keluhan.</div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-header">
                        <h2 class="card-title">Transaksi Terbaru</h2>
                        <p class="card-subtitle">Dipakai untuk menghitung poin setelah transaksi selesai.</p>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;flex-direction:column;gap:0.65rem;">
                            @forelse ($recentTransactions as $transaction)
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;border:1px solid var(--border-dark);background:var(--surface-subtle);border-radius:12px;padding:0.75rem;flex-wrap:wrap;">
                                    <div>
                                        <div style="font-weight:700;">{{ $transaction->invoice_no }}</div>
                                        <div style="font-size:0.82rem;color:var(--text-muted);">{{ $transaction->created_at->format('d M Y H:i') }} · {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}</div>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:0.65rem;flex-wrap:wrap;">
                                        <strong>Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong>
                                        <a href="{{ route('orders.show', $transaction) }}" class="btn btn-outline-light btn-small">Detail</a>
                                    </div>
                                </div>
                            @empty
                                <div style="color:var(--text-muted);">Belum ada transaksi.</div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/lucide-icons.js') }}"></script>
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
    <script>
        const membershipRulesToggle = document.getElementById('membership-rules-toggle');
        const membershipRulesPopover = document.getElementById('membership-rules-popover');

        if (membershipRulesToggle && membershipRulesPopover) {
            membershipRulesToggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const isOpen = membershipRulesPopover.classList.toggle('is-open');
                membershipRulesToggle.setAttribute('aria-expanded', String(isOpen));
            });

            document.addEventListener('click', (event) => {
                if (!membershipRulesPopover.classList.contains('is-open')) return;
                if (membershipRulesPopover.contains(event.target) || membershipRulesToggle.contains(event.target)) return;

                membershipRulesPopover.classList.remove('is-open');
                membershipRulesToggle.setAttribute('aria-expanded', 'false');
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;

                membershipRulesPopover.classList.remove('is-open');
                membershipRulesToggle.setAttribute('aria-expanded', 'false');
            });
        }
    </script>
</body>
</html>
