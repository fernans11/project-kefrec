<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeFrec Coffee Shop</title>

    <link rel="stylesheet" href="{{ asset('css/customer-landing.css') }}">
</head>
<body>

    {{-- Topbar customer (TANPA Auth::user()) --}}
    <header class="topbar">
        <div class="brand">
            <span class="brand-title">KeFrec Coffee Shop</span>
        </div>

        <nav class="top-actions">
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

    <script src="{{ asset('js/customer-landing.js') }}"></script>
</body>
</html>
