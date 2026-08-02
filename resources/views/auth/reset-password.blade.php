<x-panel-layout :title="__('Choose a new password')" :noindex="true">
    <div class="mx-auto flex w-full max-w-md flex-col justify-center px-4 py-10 sm:py-16">
        <x-nexo-auth-card>
            <h1 class="text-xl font-semibold text-ink">{{ __('Choose a new password') }}</h1>

            @if ($errors->any())
                <ul role="alert" class="mt-4 list-disc space-y-1 rounded-md bg-danger-subtle py-3 pl-8 pr-3 text-sm text-danger-subtle-fg">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" required autocomplete="username"
                           value="{{ old('email', $email) }}"
                           class="w-full rounded-lg border border-control bg-surface px-3 py-2 text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium">{{ __('New password') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           class="w-full rounded-lg border border-control bg-surface px-3 py-2 text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium">{{ __('Confirm the new password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                           class="w-full rounded-lg border border-control bg-surface px-3 py-2 text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>

                <button type="submit" class="nexo-btn nexo-btn--primary w-full">{{ __('Change my password') }}</button>
            </form>
        </x-nexo-auth-card>
    </div>
</x-panel-layout>
