<?php

it('AC-20: renders the attribution footer on public surfaces when enabled', function () {
    config([
        'nexo.attribution.enabled' => true,
        'nexo.attribution.text' => 'Powered by alvarocdev.com',
        'nexo.attribution.url' => 'https://alvarocdev.com',
    ]);

    $this->get('/')
        ->assertSee('Powered by alvarocdev.com')
        ->assertSee('https://alvarocdev.com', false);
});

it('AC-20: hides the attribution footer when disabled', function () {
    config(['nexo.attribution.enabled' => false]);

    $this->get('/')->assertDontSee('Powered by alvarocdev.com');
});
