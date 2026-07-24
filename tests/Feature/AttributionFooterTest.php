<?php

// The multi-instance, env-gated attribution (add-branding-footer standard) lives
// on the short host's minimal pages via <x-attribution>. The panel host carries
// the canonical ecosystem powered-by inside the nexo-footer instead (covered by
// tests/Feature/Nexo/EcosystemChromeTest).

it('AC-20: renders the attribution footer on the short host when enabled', function () {
    config([
        'nexo.attribution.enabled' => true,
        'nexo.attribution.text' => 'Powered by alvarocdev.com',
        'nexo.attribution.url' => 'https://alvarocdev.com',
    ]);

    $this->get(shortUrl('/report'))
        ->assertOk()
        ->assertSee('Powered by alvarocdev.com')
        ->assertSee('https://alvarocdev.com', false);
});

it('AC-20: hides the attribution footer when disabled', function () {
    config(['nexo.attribution.enabled' => false]);

    $this->get(shortUrl('/report'))->assertDontSee('Powered by alvarocdev.com');
});
