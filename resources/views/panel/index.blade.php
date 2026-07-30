<x-panel-layout :title="__('Your links')">
    <x-slot:actions>
        <form method="POST" action="{{ config('nexo-sso.enabled') ? route('nexo-sso.logout') : route('logout') }}">
            @csrf
            <button type="submit" class="nexo-btn nexo-btn--ghost">{{ __('Sign out') }}</button>
        </form>
    </x-slot:actions>

    <div class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-2xl font-semibold text-ink">{{ __('Your links') }}</h1>

        @if (session('status'))
            <p role="status" class="mt-4 inline-block rounded-md bg-success-subtle px-3 py-1.5 text-sm font-medium text-success-subtle-fg">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <ul role="alert" class="mt-4 list-disc space-y-1 rounded-md bg-danger-subtle py-3 pl-8 pr-3 text-sm text-danger-subtle-fg">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('links.store') }}" class="mt-6 space-y-4 rounded-2xl border border-line bg-surface-raised p-5 sm:p-6">
            @csrf
            <h2 class="text-base font-semibold text-ink">{{ __('Create a short link') }}</h2>
            <div>
                <label for="target_url" class="block text-sm font-medium text-muted">{{ __('Destination URL') }}</label>
                <input id="target_url" type="url" name="target_url" value="{{ old('target_url') }}" placeholder="https://…" required
                       @error('target_url') aria-invalid="true" aria-describedby="target_url-error" @enderror
                       class="mt-1 min-h-11 w-full rounded-md border border-control bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring">
                @error('target_url')
                    <p id="target_url-error" class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="custom_slug" class="block text-sm font-medium text-muted">{{ __('Custom slug (optional)') }}</label>
                <input id="custom_slug" type="text" name="custom_slug" value="{{ old('custom_slug') }}" placeholder="my-link"
                       @error('custom_slug') aria-invalid="true" aria-describedby="custom_slug-error" @enderror
                       class="mt-1 min-h-11 w-full rounded-md border border-control bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring">
                @error('custom_slug')
                    <p id="custom_slug-error" class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="nexo-btn nexo-btn--primary">{{ __('Create') }}</button>
        </form>

        @if ($links->isEmpty())
            <p class="mt-8 text-muted">{{ __('No links yet.') }}</p>
        @else
            <div class="mt-8 overflow-x-auto rounded-2xl border border-line">
                <table class="w-full text-left text-sm">
                    <thead class="bg-bg-subtle text-muted">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('Short link') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Target') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($links as $link)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-ink">{{ config('nexo.short_host') }}/{{ $link->slug }}</td>
                                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::limit($link->target_url, 40) }}</td>
                                <td class="px-4 py-3">
                                    @if ($link->is_active)
                                        <span class="inline-block rounded-full bg-success-subtle px-2.5 py-0.5 text-xs font-medium text-success-subtle-fg">{{ __('Active') }}</span>
                                    @else
                                        <span class="inline-block rounded-full bg-danger-subtle px-2.5 py-0.5 text-xs font-medium text-danger-subtle-fg">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('links.stats', $link) }}" class="font-medium text-link hover:text-link-hover hover:underline">{{ __('Stats') }}</a>
                                        @if ($link->is_active)
                                            <form method="POST" action="{{ route('links.deactivate', $link) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-md px-2 py-1 text-xs font-medium text-danger hover:bg-danger-subtle">
                                                    {{ __('Deactivate') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-panel-layout>
