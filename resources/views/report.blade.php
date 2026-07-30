<x-layout :title="__('Report a link')">
    <h1>{{ __('Report a link') }}</h1>

    @if ($sent)
        <p class="pill pill-on" role="status">{{ __('Thank you — your report has been received.') }}</p>
        <p class="follow-up"><a href="{{ '//'.config('nexo.panel_host') }}">{{ config('app.name') }}</a></p>
    @else
        @if ($invalid)
            <p class="errors">{{ __('Please provide the link and a reason.') }}</p>
        @endif

        <form method="POST" action="/report">
            <label>
                {{ __('Short link') }}
                <input type="text" name="slug" value="{{ $slug }}" required>
            </label>
            <label>
                {{ __('Reason') }}
                <select name="reason" required>
                    @foreach (config('nexo.report_reasons') as $key => $label)
                        <option value="{{ $key }}">{{ __($label) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                {{ __('Details (optional)') }}
                <textarea name="note" rows="3" maxlength="500"></textarea>
            </label>
            <button type="submit">{{ __('Submit report') }}</button>
        </form>
    @endif
</x-layout>
