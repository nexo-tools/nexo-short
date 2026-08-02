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
                    <label for="email" class="block text-sm font-medium text-muted">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" required autocomplete="username"
                           value="{{ old('email', $email) }}"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                           class="mt-1 min-h-11 w-full rounded-md border border-control bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring">
                    @error('email')
                        <p id="email-error" class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-muted">{{ __('New password') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                           class="mt-1 min-h-11 w-full rounded-md border border-control bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring">
                    @error('password')
                        <p id="password-error" class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-muted">{{ __('Confirm the new password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                           @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror
                           class="mt-1 min-h-11 w-full rounded-md border border-control bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring">
                    @error('password_confirmation')
                        <p id="password_confirmation-error" class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="nexo-btn nexo-btn--primary w-full">{{ __('Change my password') }}</button>
            </form>
        </x-nexo-auth-card>
    </div>
</x-panel-layout>
