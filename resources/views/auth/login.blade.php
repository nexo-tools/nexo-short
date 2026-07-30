<x-panel-layout :title="__('Sign in')">
    <div class="mx-auto flex w-full max-w-md flex-col justify-center px-4 py-10 sm:py-16">
        <div class="rounded-2xl border border-line bg-surface-raised p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-semibold text-ink">{{ __('Sign in') }}</h1>

            @if ($errors->any())
                <ul class="mt-4 list-disc space-y-1 rounded-md bg-danger-subtle py-3 pl-8 pr-3 text-sm text-danger-subtle-fg">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            {{-- Local credentials: hidden on an SSO-only instance (ADR-003 §4). --}}
            @if (config('nexo.auth_mode') !== 'sso')
                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-muted">{{ __('Email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="mt-1 w-full rounded-md border border-line bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-muted">{{ __('Password') }}</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="mt-1 w-full rounded-md border border-line bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-muted">
                        <input type="checkbox" name="remember" class="rounded border-line text-brand-600 focus:ring-brand-500/40"> {{ __('Remember me') }}
                    </label>
                    <button type="submit" class="nexo-btn nexo-btn--primary w-full">{{ __('Sign in') }}</button>
                </form>
            @endif

            @if (config('nexo-sso.enabled'))
                <p class="mt-4">
                    <a href="{{ route('nexo-sso.redirect') }}" class="nexo-btn nexo-btn--ghost w-full">{{ __('Continue with Nexo ID') }}</a>
                </p>
            @endif

            @if (config('nexo.auth_mode') !== 'sso' && config('nexo.allow_registration'))
                <p class="mt-6 text-sm text-muted">
                    <a href="{{ route('register') }}" class="font-medium text-link hover:text-link-hover hover:underline">{{ __('Create an account') }}</a>
                </p>
            @endif
        </div>
    </div>
</x-panel-layout>
