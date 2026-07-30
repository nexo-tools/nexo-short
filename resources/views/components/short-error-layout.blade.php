{{-- Self-contained error shell for the SHORT host (ADR-001). That domain serves
     redirects only and stays cookieless: it loads no panel chrome, no token
     stylesheet and no Vite build, so this page inlines its minimal dark styles.
     The literal hex below is deliberate and allow-listed in NoHardcodedColorsTest;
     the accent is the Nexo violet so the isolation does not cost the brand.
     Extra links (panel, report channel) go in the slot. --}}
@props(['title', 'message'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · {{ config('app.name') }}</title>
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
        .links a:hover { color: #c4b5fd; }
        .attribution { margin-top: auto; padding-top: 2rem; font-size: .8rem; }
        .attribution a { color: #8a8a8a; text-decoration: none; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>
    @unless ($slot->isEmpty())
        <div class="links">{{ $slot }}</div>
    @endunless
    <x-attribution />
</body>
</html>
