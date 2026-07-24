{{-- Stamp <html data-theme> before first paint (no FOUC). Included in the panel
     <head> BEFORE the stylesheet. Reads the persisted choice, else the OS
     preference. This is the only inline <script> on the panel host; it is
     allow-listed in the CSP by its exact sha256 hash (App\Http\Middleware\
     SecurityHeaders + public/.htaccess) — no 'unsafe-inline' for scripts.
     Recompute the hash if you edit the snippet below. --}}
<script>
    (function () {
        try {
            var stored = localStorage.getItem('nexo-theme');
            var mode = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', mode);
        } catch (e) { /* private mode: fall back to CSS prefers-color-scheme */ }
    })();
</script>
