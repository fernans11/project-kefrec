<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daftar Member - KeFrec</title>

  <link rel="stylesheet" href="{{ asset('css/register-page.css') }}">
</head>
<body>

  @if ($errors->any())
    <div style="max-width:520px;margin:16px auto;color:#fff;background:#b91c1c;padding:12px 14px;border-radius:10px;">
      <ul style="margin:0;padding-left:18px;">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form id="register-form" method="POST" action="{{ route('register') }}">
    @csrf

    {!! file_get_contents(resource_path('views/customer/_register_body.html')) !!}
  </form>

  <script>
    window.__KEFREC__ = {
      landingUrl: "{{ route('landing') }}",
      loginUrl: "{{ route('login') }}",
    };
  </script>

  <script src="{{ asset('js/register-page.js') }}"></script>
</body>
</html>
