<x-layout :title="__('Your links')">
    <div class="row" style="margin-bottom: 1.5rem;">
        <h1 style="margin: 0;">{{ __('Your links') }}</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background: #262626;">{{ __('Sign out') }}</button>
        </form>
    </div>

    @if (session('status'))
        <p class="pill pill-on" style="display: inline-block; margin-bottom: 1rem;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul class="errors">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('links.store') }}" style="margin-bottom: 2rem;">
        @csrf
        <h2 style="font-size: 1.05rem; margin: 0;">{{ __('Create a short link') }}</h2>
        <label>
            {{ __('Destination URL') }}
            <input type="url" name="target_url" value="{{ old('target_url') }}" placeholder="https://…" required>
        </label>
        <label>
            {{ __('Custom slug (optional)') }}
            <input type="text" name="custom_slug" value="{{ old('custom_slug') }}" placeholder="my-link">
        </label>
        <button type="submit">{{ __('Create') }}</button>
    </form>

    @if ($links->isEmpty())
        <p class="muted">{{ __('No links yet.') }}</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>{{ __('Short link') }}</th>
                    <th>{{ __('Target') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($links as $link)
                    <tr>
                        <td>{{ config('nexo.short_host') }}/{{ $link->slug }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($link->target_url, 40) }}</td>
                        <td>
                            @if ($link->is_active)
                                <span class="pill pill-on">{{ __('Active') }}</span>
                            @else
                                <span class="pill pill-off">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($link->is_active)
                                <form method="POST" action="{{ route('links.deactivate', $link) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" style="background: #3f1d1d; color: #fca5a5; padding: .3rem .6rem; font-size: .8rem;">
                                        {{ __('Deactivate') }}
                                    </button>
                                </form>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-layout>
