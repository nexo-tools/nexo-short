<x-panel-layout :title="__('Verify your email')" :noindex="true">
    <div class="mx-auto flex w-full max-w-md flex-col justify-center px-4 py-10 sm:py-16">
        <x-nexo-auth-card>
            <h1 class="text-xl font-semibold text-ink">{{ __('Verify your email') }}</h1>

            <p class="mt-2 text-sm text-muted">
                {{ __('We sent a link to :email. Confirming it is what lets you recover your account if you ever forget your password.', ['email' => auth()->user()->email]) }}
            </p>

            @if (session('status'))
                <p class="nexo-flash mt-4" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
                @csrf
                <button type="submit" class="nexo-btn nexo-btn--primary w-full">{{ __('Resend the link') }}</button>
            </form>

            <p class="mt-6 text-sm">
                <a href="{{ route('panel') }}" class="text-link underline">{{ __('Back to my links') }}</a>
            </p>
        </x-nexo-auth-card>
    </div>
</x-panel-layout>
