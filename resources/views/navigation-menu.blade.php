<nav class="navbar">
    <div class="container navbar-inner" style="max-width: 1200px;">

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
            {{-- Tombol kembali ke Home (landing member) --}}
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-small" style="white-space:nowrap;">
                Kembali
            </a>

            {{-- Profile dropdown --}}
            @php
                $name = Auth::user()->name ?? 'Member';
                $email = Auth::user()->email ?? '';
                $role = Auth::user()->usertype ?? 'member';
                $initial = mb_strtoupper(mb_substr($name, 0, 1));
            @endphp

            <div class="profile-wrap" x-data="{ open:false }" @click.outside="open=false" style="position:relative;">
                <button class="profile-chip" type="button" @click="open=!open" :aria-expanded="open ? 'true':'false'">
                    <span class="profile-avatar">{{ $initial }}</span>
                    <span class="profile-name">{{ $name }}</span>
                    <span class="profile-caret">▾</span>
                </button>

                <div class="profile-menu" :class="open ? 'is-open' : ''" role="menu" aria-label="Menu Profil">
                    <div style="padding: 0.55rem 0.65rem 0.35rem;">
                        <div style="font-weight:700; font-size:0.92rem; line-height:1.2;">{{ $name }}</div>
                        <div style="opacity:.85; font-size:0.82rem; margin-top:0.15rem;">{{ $email }}</div>
                        <div style="opacity:.8; font-size:0.78rem; margin-top:0.15rem;">{{ $role }}</div>
                    </div>

                    <div class="profile-divider"></div>

                    <a class="profile-item" href="{{ route('home') }}" role="menuitem">
                        Member
                    </a>

                    <a class="profile-item" href="{{ route('orders.index') }}" role="menuitem">
                        Pesanan Saya
                    </a>

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
        </div>

    </div>
</nav>
