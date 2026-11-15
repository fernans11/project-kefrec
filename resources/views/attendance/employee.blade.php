<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Karyawan - KeFrec Coffee Shop</title>

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
                <a href="{{ route($backRoute) }}" class="btn btn-outline-light btn-small">Kembali</a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-red btn-small" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="page">
        <div class="container">
            <section class="card" style="max-width:760px;margin:0 auto;">
                <div class="card-header">
                    <h3 class="card-title">Absensi Karyawan</h3>
                    <p class="card-subtitle">Pilih nama Anda lalu lakukan check-in atau check-out. Data akan masuk ke admin panel Absensi.</p>
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
                    @if ($errors->any())
                        <div style="margin-bottom:0.8rem;padding:0.6rem 0.8rem;border-radius:10px;background:rgba(244,67,54,0.16);border:1px solid rgba(244,67,54,0.55);color:var(--red-main);font-weight:600;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div style="padding:1rem;border-radius:14px;border:1px solid var(--border-dark);background:var(--surface-subtle);box-shadow:0 10px 24px rgba(72,47,31,0.08);">
                        <label for="staff_id" style="display:block;font-weight:600;margin-bottom:0.45rem;color:var(--text-main);">Nama Karyawan</label>
                        <select id="staff_id" name="staff_id" required
                            style="width:100%;padding:0.7rem 0.85rem;border-radius:10px;border:1px solid var(--border-dark);background:var(--bg-input);color:var(--text-main);font-size:0.95rem;font-weight:500;">
                            <option value="">Pilih karyawan</option>
                            @foreach ($staff as $person)
                                <option value="{{ $person->id }}">{{ $person->name }}{{ $person->position ? ' - ' . $person->position : '' }}</option>
                            @endforeach
                        </select>

                        <div style="display:flex;gap:0.7rem;flex-wrap:wrap;margin-top:1rem;">
                            <form method="POST" action="{{ route('attendance.check-in') }}" style="margin:0;flex:1;min-width:180px;">
                                @csrf
                                <input type="hidden" name="staff_id" id="staff_id_in">
                                <button class="btn btn-outline-light" type="submit" style="width:100%;">Check-in</button>
                            </form>

                            <form method="POST" action="{{ route('attendance.check-out') }}" style="margin:0;flex:1;min-width:180px;">
                                @csrf
                                <input type="hidden" name="staff_id" id="staff_id_out">
                                <button class="btn btn-red" type="submit" style="width:100%;">Check-out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        const staffSelect = document.getElementById('staff_id');
        const staffIn = document.getElementById('staff_id_in');
        const staffOut = document.getElementById('staff_id_out');

        function syncStaff() {
            staffIn.value = staffSelect.value;
            staffOut.value = staffSelect.value;
        }

        staffSelect.addEventListener('change', syncStaff);
        syncStaff();
    </script>
    <script src="{{ asset('js/lucide-icons.js') }}"></script>
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
</body>
</html>
