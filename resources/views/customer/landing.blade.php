<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KeFrec Coffee Shop</title>

    <link rel="stylesheet" href="{{ asset('css/customer-landing.css') }}">
</head>
<body>

<script>
    window.__KEFREC__ = {
        loginUrl: "{{ route('login') }}",
        registerUrl: "{{ route('register') }}",
        productsApiUrl: "{{ url('/api/products') }}",
        ordersUrl: "{{ route('orders.index') }}",
        // Jetstream profile URL default biasanya /user/profile
        profileUrl: "{{ url('/user/profile') }}",
        homeUrl: "{{ route('home') }}",
        landingUrl: "{{ route('landing') }}",
    };
</script>

<div id="kefrec-root">

    <!-- ================= TOPBAR ================= -->
    <header class="navbar">
        <div class="container navbar-inner">

            <div class="logo-area">
                <div class="logo-box">
                    <img src="" alt="Logo KeFrec"
                         onerror="this.style.display='none'; this.parentElement.querySelector('.logo-fallback').style.display='block';">
                    <span class="logo-fallback" style="display:none;">☕</span>
                </div>
                <div class="brand-text">
                    <span class="brand-title">KeFrec CoffeeShop</span>
                </div>
            </div>

            <div class="right-area">
                @auth
                    @php
                        $name = auth()->user()->name ?? 'Member';
                        $email = auth()->user()->email ?? '';
                        $role = auth()->user()->usertype ?? 'member';
                        $initial = mb_strtoupper(mb_substr($name, 0, 1));
                    @endphp

                    <div class="profile-wrap" id="profileWrap">
                        <button class="profile-chip" type="button" id="profileToggle" aria-expanded="false">
                            <span class="profile-avatar">{{ $initial }}</span>
                            <span class="profile-name">{{ $name }}</span>
                            <span class="profile-caret">▾</span>
                        </button>

                        <div class="profile-menu" id="profileMenu" role="menu" aria-label="Menu Profil">
                            <div style="padding: 0.55rem 0.65rem 0.35rem;">
                                <div style="font-weight:700; font-size:0.92rem; line-height:1.2;">
                                    {{ $name }}
                                </div>
                                <div style="opacity:.85; font-size:0.82rem; margin-top:0.15rem;">
                                    {{ $email }}
                                </div>
                                <div style="opacity:.8; font-size:0.78rem; margin-top:0.15rem;">
                                    {{ $role }}
                                </div>
                            </div>

                            <div class="profile-divider"></div>

                            <!-- Member (sementara arahkan ke /home) -->
                            <a class="profile-item" href="{{ route('home') }}" role="menuitem">
                                Member
                            </a>

                            <a class="profile-item" href="{{ route('orders.index') }}" role="menuitem">
                                Pesanan Saya
                            </a>

                            <!-- Pengaturan Jetstream -->
                            <a class="profile-item" href="{{ url('/user/profile') }}" role="menuitem">
                                Pengaturan
                            </a>

                            <div class="profile-divider"></div>

                            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                                @csrf
                                <button class="profile-item profile-logout" type="submit" role="menuitem">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Guest: hanya Login (tanpa Daftar di navbar) -->
                    <a href="{{ route('login') }}" class="btn btn-red" id="btn-login">Login</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- ================= CONTENT ================= -->
    <main class="container">

        <!-- HERO -->
        <section class="card card-highlight hero-card">
            <div class="hero-image-wrapper">
                <img
                    class="hero-image"
                    src="https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=1400&q=80"
                    alt="Interior KeFrec CoffeeShop"
                />
                <div class="hero-gradient"></div>
                <div class="hero-content">
                    <h2 class="hero-title">Selamat Datang di KeFrec</h2>
                    <p class="hero-text">
                        Nikmati kopi berkualitas dan makanan lezat setiap hari.
                    </p>

                    @guest
                        <a href="{{ route('register') }}" class="btn btn-red btn-small" id="btn-daftar-member">
                            ★ Daftar Member
                        </a>
                    @else
                        <a href="#menu-kami" class="btn btn-red btn-small" id="btn-lihat-menu">
                            Lihat Menu
                        </a>
                    @endguest
                </div>
            </div>
        </section>

        <!-- MEMBER BENEFITS (pixel-accurate style: 3 card + tombol Lihat Pesanan) -->
        @auth
            <section class="card" style="margin-top: 16px; padding: 16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="opacity:.9;">📍</span>
                        <div>
                            <div style="font-weight:700;">Member Benefits</div>
                            <div style="opacity:.8;font-size:.88rem;">Hai, {{ auth()->user()->name }}.</div>
                        </div>
                    </div>

                    <a href="{{ route('orders.index') }}" class="btn btn-outline-light" style="white-space:nowrap;">
                        Lihat Pesanan
                    </a>
                </div>

                <div class="benefit-grid" style="margin-top: 14px;">
                    <div class="benefit-card">
                        <div class="benefit-icon">❤️</div>
                        <div class="benefit-title">Poin Anda</div>
                        <div class="benefit-desc">
                            <strong>120</strong> poin
                        </div>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">📈</div>
                        <div class="benefit-title">Total Belanja</div>
                        <div class="benefit-desc">
                            <strong>Rp 850.000</strong>
                        </div>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">⭐</div>
                        <div class="benefit-title">Status</div>
                        <div class="benefit-desc">
                            <span class="member-badge">Gold</span>
                        </div>
                    </div>
                </div>
            </section>
        @endauth

        <!-- BODY CUSTOMER -->
        <div id="menu-kami"></div>
        {!! file_get_contents(base_path('resources/views/customer/_landing_body.html')) !!}
    </main>
</div>

<script src="{{ asset('js/customer-landing.js') }}"></script>
</body>
</html>
