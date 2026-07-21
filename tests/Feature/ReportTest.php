<?php

use App\Models\Report;

it('AC-37: the report form loads on the short host without auth', function () {
    $this->get(shortUrl('/report'))
        ->assertOk()
        ->assertSee(__('Report a link'))
        ->assertHeader('X-Robots-Tag', 'noindex');
});

it('AC-37: a valid report is stored', function () {
    $response = $this->post(shortUrl('/report'), [
        'slug' => 'abc123',
        'reason' => 'malicious',
        'note' => 'phishing page',
    ]);

    $response->assertOk()->assertSee(__('Thank you — your report has been received.'));

    $report = Report::sole();
    expect($report->slug)->toBe('abc123');
    expect($report->reason)->toBe('malicious');
});

it('AC-37: an invalid reason is rejected and stores nothing', function () {
    $this->post(shortUrl('/report'), ['slug' => 'abc123', 'reason' => 'nonsense'])
        ->assertStatus(422);

    expect(Report::count())->toBe(0);
});

it('AC-38: the report form is rate-limited per IP', function () {
    config(['nexo.report_rate.per_ip' => 2]);

    collect(range(1, 2))->each(function () {
        $this->post(shortUrl('/report'), ['slug' => 'abc123', 'reason' => 'spam']);
    });

    $this->post(shortUrl('/report'), ['slug' => 'abc123', 'reason' => 'spam'])->assertStatus(429);

    expect(Report::count())->toBe(2);
});
