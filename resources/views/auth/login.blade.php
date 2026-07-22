<x-layout :title="__('Sign in')">
    <h1>{{ __('Sign in') }}</h1>

    @if ($errors->any())
        <ul class="errors">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- Local credentials: hidden on an SSO-only instance (ADR-003 §4). --}}
    @if (config('nexo.auth_mode') !== 'sso')
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label>
                {{ __('Email') }}
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </label>
            <label>
                {{ __('Password') }}
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <label class="muted" style="flex-direction: row; align-items: center; gap: .5rem;">
                <input type="checkbox" name="remember" style="width: auto;"> {{ __('Remember me') }}
            </label>
            <button type="submit">{{ __('Sign in') }}</button>
        </form>
    @endif

    @if (config('nexo-sso.enabled'))
        <p style="margin-top: 1rem;">
            <a href="{{ route('nexo-sso.redirect') }}">{{ __('Continue with Nexo ID') }}</a>
        </p>
    @endif

    @if (config('nexo.auth_mode') !== 'sso' && config('nexo.allow_registration'))
        <p class="muted" style="margin-top: 1.25rem;">
            <a href="{{ route('register') }}">{{ __('Create an account') }}</a>
        </p>
    @endif
</x-layout>
