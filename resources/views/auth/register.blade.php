<x-layout :title="__('Create an account')">
    <h1>{{ __('Create an account') }}</h1>

    @if ($errors->any())
        <ul class="errors">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label>
            {{ __('Name') }}
            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </label>
        <label>
            {{ __('Email') }}
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        </label>
        <label>
            {{ __('Password') }}
            <input type="password" name="password" required autocomplete="new-password">
        </label>
        <label>
            {{ __('Confirm password') }}
            <input type="password" name="password_confirmation" required autocomplete="new-password">
        </label>
        <button type="submit">{{ __('Create an account') }}</button>
    </form>

    <p class="muted" style="margin-top: 1.25rem;">
        <a href="{{ route('login') }}">{{ __('Already have an account? Sign in') }}</a>
    </p>
</x-layout>
