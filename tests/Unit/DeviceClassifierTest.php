<?php

use App\Support\DeviceClassifier;

it('AC-24: classifies a bot User-Agent as bot', function (string $ua) {
    expect(DeviceClassifier::classify($ua))->toBe('bot');
})->with([
    'Googlebot/2.1 (+http://www.google.com/bot.html)',
    'Mozilla/5.0 (compatible; bingbot/2.0)',
    'curl/8.4.0',
    'python-requests/2.31.0',
]);

it('AC-24: classifies a missing User-Agent as bot', function () {
    expect(DeviceClassifier::classify(null))->toBe('bot');
    expect(DeviceClassifier::classify(''))->toBe('bot');
});

it('AC-24: classifies a mobile User-Agent as mobile', function () {
    $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148';
    expect(DeviceClassifier::classify($ua))->toBe('mobile');
});

it('AC-24: classifies a desktop User-Agent as desktop', function () {
    $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36';
    expect(DeviceClassifier::classify($ua))->toBe('desktop');
});
