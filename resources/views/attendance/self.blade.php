<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Staff - KeFrec</title>
    <style>
        :root {
            color-scheme: dark;
        }
        body {
            margin: 0;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #1f1f1f;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .card {
            width: 100%;
            max-width: 520px;
            background: #2a2a2a;
            border: 1px solid #3a3a3a;
            border-radius: 16px;
            padding: 1.6rem 1.8rem;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.5);
        }
        h1 {
            margin: 0 0 0.6rem;
            font-size: 1.35rem;
        }
        p {
            margin: 0 0 1.2rem;
            color: #d0d0d0;
            font-size: 0.9rem;
        }
        label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }
        select {
            width: 100%;
            padding: 0.55rem 0.7rem;
            border-radius: 10px;
            border: 1px solid #3a3a3a;
            background: #1f1f1f;
            color: #ffffff;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .actions {
            display: flex;
            gap: 0.6rem;
        }
        button {
            flex: 1;
            border: none;
            border-radius: 999px;
            padding: 0.6rem 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-in {
            background: #2e7d32;
            color: #ffffff;
        }
        .btn-out {
            background: #c62828;
            color: #ffffff;
        }
        .alert {
            padding: 0.6rem 0.8rem;
            border-radius: 10px;
            margin-bottom: 0.8rem;
            font-size: 0.85rem;
        }
        .alert-success {
            background: rgba(46, 125, 50, 0.2);
            border: 1px solid rgba(46, 125, 50, 0.7);
        }
        .alert-error {
            background: rgba(198, 40, 40, 0.2);
            border: 1px solid rgba(198, 40, 40, 0.7);
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Absensi Staff</h1>
        <p>Silakan pilih nama Anda lalu klik Check-in atau Check-out.</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('attendance.check-in') }}">
            @csrf
            <label for="staff_id">Nama Staff</label>
            <select id="staff_id" name="staff_id" required>
                <option value="">Pilih staff</option>
                @foreach ($staff as $person)
                    <option value="{{ $person->id }}">{{ $person->name }}{{ $person->position ? ' - ' . $person->position : '' }}</option>
                @endforeach
            </select>

            <div class="actions">
                <button class="btn-in" type="submit">Check-in</button>
            </div>
        </form>

        <form method="POST" action="{{ route('attendance.check-out') }}" style="margin-top: 0.8rem;">
            @csrf
            <input type="hidden" name="staff_id" id="staff_id_out">
            <div class="actions">
                <button class="btn-out" type="submit">Check-out</button>
            </div>
        </form>
    </div>

    <script>
        const staffSelect = document.getElementById('staff_id');
        const staffOut = document.getElementById('staff_id_out');
        staffSelect.addEventListener('change', () => {
            staffOut.value = staffSelect.value;
        });
    </script>
</body>
</html>
