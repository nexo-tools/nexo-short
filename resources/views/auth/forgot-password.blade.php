<x-panel-layout :title="__('Forgot your password?')" :noindex="true">
    <div class="mx-auto flex w-full max-w-md flex-col justify-center px-4 py-10 sm:py-16">
        <x-nexo-auth-card>
            <h1 class="text-xl font-semibold text-ink">{{ __('Forgot your password?') }}</h1>

            <p class="mt-2 text-sm text-muted">
                {{ __('Tell us your email and we send you a link to choose a new password.') }}
            </p>

            @if (session('status'))
                <p class="nexo-flash mt-4" role="status">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <ul role="alert" class="mt-4 list-disc space-y-1 rounded-md bg-danger-subtle py-3 pl-8 pr-3 text-sm text-danger-subtle-fg">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-muted">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" required autofocus autocomplete="username"
                           value="{{ old('email') }}"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                           class="mt-1 min-h-11 w-full rounded-md border border-control bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring">
                    @error('email')
                        <p id="email-error" class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="nexo-btn nexo-btn--primary w-full">{{ __('Send the link') }}</button>
            </form>

            <p class="mt-6 text-sm">
                <a href="{{ route('login') }}" class="text-link underline">{{ __('Back to sign in') }}</a>
            </p>
        </x-nexo-auth-card>
    </div>
</x-panel-layout>
