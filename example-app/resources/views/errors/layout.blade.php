<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · SchoolHub</title>
    {{-- Inline styles so error pages work even if the asset build is missing --}}
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f1f5f9; font-family: 'Segoe UI', Arial, sans-serif; color: #0f172a; padding: 16px; box-sizing: border-box; }
        .box { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 40px 32px; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 1px 3px rgb(0 0 0 / .06); }
        .brand { color: #1b6ac9; font-weight: 700; letter-spacing: 1px; font-size: 14px; }
        .code { font-size: 56px; font-weight: 800; color: #1656a3; margin: 12px 0 4px; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        p { font-size: 17px; color: #475569; line-height: 1.5; margin: 0 0 24px; }
        a { display: inline-block; background: #1b6ac9; color: #fff; text-decoration: none; padding: 12px 22px; border-radius: 6px; font-weight: 600; font-size: 16px; }
        a:hover { background: #1656a3; }
    </style>
</head>
<body>
    <div class="box">
        <div class="brand">SCHOOLHUB</div>
        <div class="code">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}">Go to my dashboard</a>
    </div>
</body>
</html>
