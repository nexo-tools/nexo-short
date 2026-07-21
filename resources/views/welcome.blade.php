<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 1.5rem; padding: 2rem;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #0a0a0a; color: #fafafa; text-align: center;
        }
        h1 { margin: 0; font-size: clamp(1.8rem, 5vw, 3rem); }
        p { margin: 0; color: #a3a3a3; max-width: 40rem; }
        nav { display: flex; gap: .75rem; font-size: .85rem; }
        nav a { color: #a3a3a3; text-decoration: none; }
        nav a:hover { color: #fafafa; }
        .attribution { margin-top: auto; padding-top: 2rem; font-size: .8rem; }
        .attribution a { color: #737373; text-decoration: none; }
        .attribution a:hover { color: #a3a3a3; }
    </style>
</head>
<body>
    <h1>{{ config('app.name') }}</h1>
    <p>{{ __('Short links on your own domain') }}</p>
    <p>{{ __('Cookieless click metrics. Privacy by design. Self-hostable.') }}</p>
    <nav>
        <a href="?lang=en">English</a>
        <a href="?lang=es">Español</a>
        <a href="?lang=pt">Português</a>
    </nav>
    <nav>
        <a href="{{ route('privacy') }}">{{ __('Privacy') }}</a>
        <a href="{{ route('terms') }}">{{ __('Terms') }}</a>
    </nav>
    <x-attribution />
</body>
</html>
