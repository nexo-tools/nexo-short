<x-panel-layout :title="__('Create an account')">
    <div class="mx-auto flex w-full max-w-md flex-col justify-center px-4 py-10 sm:py-16">
        <div class="rounded-2xl border border-line bg-surface-raised p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-semibold text-ink">{{ __('Create an account') }}</h1>

            @if ($errors->any())
                <ul class="mt-4 list-disc space-y-1 rounded-md bg-danger-subtle py-3 pl-8 pr-3 text-sm text-danger-subtle-fg">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-muted">{{ __('Name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                           class="mt-1 w-full rounded-md border border-line bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-muted">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                           class="mt-1 w-full rounded-md border border-line bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-muted">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="mt-1 w-full rounded-md border border-line bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-muted">{{ __('Confirm password') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="mt-1 w-full rounded-md border border-line bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                </div>
                <button type="submit" class="nexo-btn nexo-btn--primary w-full">{{ __('Create an account') }}</button>
            </form>

            <p class="mt-6 text-sm text-muted">
                <a href="{{ route('login') }}" class="font-medium text-link hover:text-link-hover hover:underline">{{ __('Already have an account? Sign in') }}</a>
            </p>
        </div>
    </div>
</x-panel-layout>
