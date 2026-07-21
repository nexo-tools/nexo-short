<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('Link not found') }} · {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 1rem; padding: 2rem;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #0a0a0a; color: #fafafa; text-align: center;
        }
        h1 { margin: 0; font-size: clamp(1.5rem, 4vw, 2.2rem); }
        p { margin: 0; color: #a3a3a3; max-width: 34rem; }
        .links { display: flex; gap: 1.25rem; margin-top: .5rem; font-size: .9rem; }
        .links a { color: #a3a3a3; text-decoration: none; }
        .links a:hover { color: #fafafa; }
        .attribution { margin-top: auto; padding-top: 2rem; font-size: .8rem; }
        .attribution a { color: #737373; text-decoration: none; }
    </style>
</head>
<body>
    <h1>{{ __('Link not found') }}</h1>
    <p>{{ __('This short link does not exist or is no longer active.') }}</p>
    <div class="links">
        <a href="{{ '//'.config('nexo.panel_host') }}">{{ config('app.name') }}</a>
        <a href="{{ '//'.config('nexo.short_host').'/report' }}">{{ __('Report this link') }}</a>
    </div>
    <x-attribution />
</body>
</html>
