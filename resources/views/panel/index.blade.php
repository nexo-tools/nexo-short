<x-layout :title="__('Your links')">
    <div class="row" style="margin-bottom: 1.5rem;">
        <h1 style="margin: 0;">{{ __('Your links') }}</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background: #262626;">{{ __('Sign out') }}</button>
        </form>
    </div>

    {{-- Create form and deactivate actions land in task 1.8. --}}

    @if ($links->isEmpty())
        <p class="muted">{{ __('No links yet.') }}</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>{{ __('Short link') }}</th>
                    <th>{{ __('Target') }}</th>
                    <th>{{ __('Status') }}</th>
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
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-layout>
