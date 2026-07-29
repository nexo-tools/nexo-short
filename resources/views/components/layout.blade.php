{{-- Self-contained shell for the SHORT host (ADR-001): the report form lives on
     the cookieless domain, which loads no panel chrome, no token stylesheet and no
     Vite build, so it inlines its minimal dark styles. The literal hex below is
     deliberate and allow-listed in NoHardcodedColorsTest; the accent is the Nexo
     violet (--nexo-violet-600/700/300) so the isolation does not cost the brand. --}}
@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; flex-direction: column;
            align-items: center; gap: 1.5rem; padding: 2rem;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #0a0a0a; color: #fafafa;
        }
        main { width: 100%; max-width: 34rem; margin: auto 0; }
        h1 { font-size: 1.6rem; margin: 0 0 1.25rem; }
        a { color: #c4b5fd; }
        form { display: flex; flex-direction: column; gap: .9rem; }
        label { display: flex; flex-direction: column; gap: .35rem; font-size: .9rem; color: #d4d4d4; }
        input, textarea, select {
            padding: .6rem .7rem; border-radius: .5rem; border: 1px solid #333;
            background: #141414; color: #fafafa; font-size: 1rem; font-family: inherit;
        }
        button {
            padding: .65rem 1rem; border: 0; border-radius: .5rem; cursor: pointer;
            background: #7c3aed; color: #fff; font-size: 1rem; font-weight: 600;
        }
        button:hover { background: #6d28d9; }
        .muted { color: #a3a3a3; font-size: .9rem; }
        .errors { color: #fca5a5; font-size: .9rem; margin: 0 0 1rem; padding-left: 1.1rem; }
        .row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
        table { width: 100%; border-collapse: collapse; font-size: .92rem; }
        th, td { text-align: left; padding: .55rem .5rem; border-bottom: 1px solid #262626; }
        .attribution { margin-top: 2rem; font-size: .8rem; }
        .attribution a { color: #737373; text-decoration: none; }
        .pill { font-size: .72rem; padding: .1rem .5rem; border-radius: 1rem; }
        .pill-on { background: #14532d; color: #86efac; }
        .pill-off { background: #3f1d1d; color: #fca5a5; }
    </style>
</head>
<body>
    <main>{{ $slot }}</main>
    <x-attribution />
</body>
</html>
