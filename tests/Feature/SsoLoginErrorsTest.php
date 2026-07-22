<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

// Gate 3 dependency (nexo-id): the SSO controller redirects its failures to the
// login page with a `nexo_sso` error bag; the login view must render them.
it('renders nexo_sso errors on the login page after a failed SSO start', function () {
    config([
        'nexo-sso.enabled' => true,
        'nexo-sso.issuer' => 'https://nexoid.test',
        'nexo-sso.client_id' => '11111111-2222-3333-4444-555555555555',
    ]);
    Http::fake(fn () => throw new ConnectionException('provider down'));

    // Provider unreachable → redirect to login carrying the nexo_sso error.
    $this->get(route('nexo-sso.redirect'))->assertRedirect(route('login'));

    // The login page renders that flashed error (what nexo-id's Gate 3 relies on).
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(__('Sign-in with Nexo ID is temporarily unavailable. Please try again later.'));
});
