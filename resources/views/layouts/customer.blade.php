<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeFrec Coffee Shop</title>

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

    {{-- Topbar customer (TANPA Auth::user()) --}}
    <header class="topbar">
        <div class="brand">
            <span class="brand-title">KeFrec Coffee Shop</span>
        </div>

        <nav class="top-actions">
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" aria-pressed="false">
                <span class="theme-toggle-icon" data-theme-icon data-lucide="moon"></span>
                <span class="theme-toggle-text" data-theme-text>Dark</span>
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
            @endauth
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <script src="{{ asset('js/lucide-icons.js') }}"></script>
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
</body>
</html>
