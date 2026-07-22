<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/NexoSsoTestSupport.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config([
        'nexo-sso.enabled' => true,
        'nexo-sso.issuer' => 'https://nexoid.test',
        'nexo-sso.client_id' => '11111111-2222-3333-4444-555555555555',
    ]);
});

test('AC-CFG-1: with SSO disabled the endpoints return 404 and no network is touched', function (): void {
    config(['nexo-sso.enabled' => false]);
    Http::fake();

    $this->get('/auth/nexo/redirect')->assertNotFound();
    $this->get('/auth/nexo/callback')->assertNotFound();
    Http::assertNothingSent();
});

test('AC-CFG-2: endpoints come from the discovery document and the document is cached', function (): void {
    nexoSsoFakeProvider();

    $first = $this->get(route('nexo-sso.redirect'));
    $first->assertRedirect();
    expect($first->headers->get('Location'))->toStartWith('https://nexoid.test/oauth/authorize?');

    $this->get(route('nexo-sso.redirect'))->assertRedirect();

    Http::assertSentCount(1); // second flow start reused the cached discovery
});
