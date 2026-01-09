<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'KeFrec CoffeeShop') }}</title>

    {{-- Reuse theme KeFrec --}}
    <link rel="stylesheet" href="{{ asset('css/customer-landing.css') }}">

    {{-- Jetstream / Livewire --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* ===== KeFrec skin for Jetstream pages ===== */
        body.kefrec-auth {
            background: var(--bg-main);
            color: var(--text-main);
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .kefrec-auth .kefrec-page {
            padding: 1.5rem 0 2.5rem;
        }

        /* Make Jetstream content feel like KeFrec cards */
        .kefrec-auth .kefrec-card {
            background: var(--bg-card);
            border: 1px solid #333;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .kefrec-auth .kefrec-card-header {
            padding: 1.1rem 1.3rem 0.7rem;
            border-bottom: 1px solid var(--border-dark);
        }

        .kefrec-auth .kefrec-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kefrec-auth .kefrec-card-subtitle {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .kefrec-auth .kefrec-card-body {
            padding: 1rem 1.3rem 1.3rem;
        }

        /* Inputs */
        .kefrec-auth input[type="text"],
        .kefrec-auth input[type="email"],
        .kefrec-auth input[type="password"],
        .kefrec-auth input[type="tel"],
        .kefrec-auth select,
        .kefrec-auth textarea {
            background: #2a2222 !important;
            color: #fff !important;
            border: 1px solid var(--border-dark) !important;
            border-radius: 10px !important;
        }

        .kefrec-auth label,
        .kefrec-auth .text-gray-700,
        .kefrec-auth .text-gray-600,
        .kefrec-auth .text-gray-500 {
            color: rgba(255,255,255,.85) !important;
        }

        /* Primary buttons */
        .kefrec-auth button.bg-gray-800,
        .kefrec-auth button.bg-indigo-600,
        .kefrec-auth a.bg-indigo-600 {
            background: var(--red-main) !important;
            border: 1px solid var(--border-dark) !important;
            border-radius: 999px !important;
        }

        .kefrec-auth button.bg-gray-800:hover,
        .kefrec-auth button.bg-indigo-600:hover,
        .kefrec-auth a.bg-indigo-600:hover {
            background: var(--red-hover) !important;
        }

        /* Secondary buttons */
        .kefrec-auth button.bg-white,
        .kefrec-auth a.bg-white {
            background: transparent !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-dark) !important;
            border-radius: 999px !important;
        }

        .kefrec-auth button.bg-white:hover,
        .kefrec-auth a.bg-white:hover {
            background: #3a3a3a !important;
        }

        /* Remove white panels feel */
        .kefrec-auth .bg-white,
        .kefrec-auth .shadow,
        .kefrec-auth .shadow-sm {
            background: transparent !important;
            box-shadow: none !important;
        }
    </style>
</head>

<body class="kefrec-auth antialiased">
    <div class="min-h-screen">
        @livewire('navigation-menu')

        @if (isset($header))
            <header class="kefrec-page">
                <div class="container" style="max-width: 980px;">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="kefrec-page">
            <div class="container" style="max-width: 980px;">
                {{ $slot }}
            </div>
        </main>
    </div>

    @stack('modals')
    @livewireScripts
</body>
</html>
