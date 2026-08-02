{{-- Operator-facing (not translated): goes to whoever runs the instance. --}}
<x-nexo-mail::layout title="Reported link" :preheader="'A short link was reported: '.$report->slug">
    <h1 class="nexo-ink" style="margin:0 0 16px; font-size:20px; line-height:1.3; font-weight:700; color:#18181b;">
        A short link was reported
    </h1>

    <x-nexo-mail::panel :rows="[
        'Slug' => $report->slug,
        'Reason' => $report->reason,
        'Reported at' => $report->created_at?->toDateTimeString(),
    ]" />

    @if ($report->note)
        <p style="margin:0 0 4px; font-size:14px; line-height:1.6;"><strong>Note</strong></p>
        <p class="nexo-panel nexo-ink" style="margin:0 0 20px; padding:12px 14px; background-color:#fafafa; border-radius:8px; font-size:14px; line-height:1.6; white-space:pre-line; color:#18181b;">{{ $report->note }}</p>
    @endif

    <p class="nexo-muted" style="margin:0 0 4px; font-size:13px; line-height:1.6; color:#71717a;">
        To take it down (the redirect stops resolving, the row is kept):
    </p>
    <x-nexo-mail::code>php artisan nexo:link-deactivate {{ $report->slug }}</x-nexo-mail::code>
</x-nexo-mail::layout>
