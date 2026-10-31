<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Pelita</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    {{-- Inline styles so error pages work even if the asset build is missing --}}
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(60rem 40rem at 6% -10%, rgb(253 186 116 / .45), transparent 60%), radial-gradient(46rem 40rem at 90% 110%, rgb(251 146 160 / .25), transparent 60%), #f8f2e9; font-family: 'Nunito', 'Segoe UI', Arial, sans-serif; color: #241b14; padding: 16px; box-sizing: border-box; }
        .box { background: #fff; border: 1px solid #eadfce; border-radius: 20px; padding: 40px 32px; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 1px 3px rgb(146 64 14 / .08); }
        .brand { color: #b45309; font-weight: 800; letter-spacing: 2px; font-size: 14px; }
        .code { font-size: 56px; font-weight: 800; color: #92400e; margin: 12px 0 4px; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        p { font-size: 17px; color: #5e4f40; line-height: 1.5; margin: 0 0 24px; }
        a { display: inline-block; background: #b45309; color: #fff; text-decoration: none; padding: 12px 22px; border-radius: 12px; font-weight: 700; font-size: 16px; }
        a:hover { background: #92400e; }
    </style>
</head>
<body>
    <div class="box">
        <div class="brand">PELITA</div>
        <div class="code">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}">Go to my dashboard</a>
    </div>
</body>
</html>
