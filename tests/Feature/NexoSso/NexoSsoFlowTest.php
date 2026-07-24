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

test('AC-FLOW-1: the authorize redirect carries code+PKCE(S256)+state and the exact callback', function (): void {
    nexoSsoFakeProvider();

    $response = $this->get(route('nexo-sso.redirect'));

    $response->assertRedirect();
    parse_str((string) parse_url((string) $response->headers->get('Location'), PHP_URL_QUERY), $query);

    expect($query['response_type'])->toBe('code')
        ->and($query['client_id'])->toBe(config('nexo-sso.client_id'))
        ->and($query['redirect_uri'])->toBe(route('nexo-sso.callback'))
        ->and($query['scope'])->toBe('openid profile email')
        ->and($query['state'])->toBe(session('nexo_sso.state'))
        ->and($query['code_challenge_method'])->toBe('S256')
        ->and($query['code_challenge'])->toBe(
            rtrim(strtr(base64_encode(hash('sha256', session('nexo_sso.verifier'), true)), '+/', '-_'), '=')
        );
});

test('AC-FLOW-2: a mismatched or missing state is rejected before any provider call', function (): void {
    Http::fake();

    // Mismatched state
    $this->withSession(['nexo_sso.state' => str_repeat('s', 40), 'nexo_sso.verifier' => str_repeat('v', 64)])
        ->get(route('nexo-sso.callback', ['code' => 'auth-code', 'state' => 'evil-state']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('nexo_sso');
    $this->assertGuest();

    // No state in session at all
    $this->get(route('nexo-sso.callback', ['code' => 'auth-code', 'state' => str_repeat('s', 40)]))
        ->assertRedirect(route('login'));
    $this->assertGuest();

    Http::assertNothingSent();
});

test('AC-FLOW-2: the user denying consent (provider error, no code) redirects to login with a safe error and no session', function (): void {
    Http::fake();

    nexoSsoDeniedCallback($this)
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('nexo_sso');
    $this->assertGuest();

    // Denial short-circuits before any token exchange.
    Http::assertNothingSent();
});

test('AC-FLOW-3: a tampered id_token signature aborts login with a safe error', function (): void {
    $foreignKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($foreignKey, $foreignPem);
    nexoSsoFakeProvider([
        'access_token' => 'fake', 'token_type' => 'Bearer',
        'id_token' => nexoSsoIdToken([], $foreignPem),
    ]);

    nexoSsoCallback($this)->assertRedirect(route('login'))->assertSessionHasErrors('nexo_sso');
    $this->assertGuest();
});

test('AC-FLOW-3: an expired id_token aborts login with a safe error', function (): void {
    nexoSsoFakeProvider([
        'access_token' => 'fake', 'token_type' => 'Bearer',
        'id_token' => nexoSsoIdToken(['exp' => time() - 120]),
    ]);

    nexoSsoCallback($this)->assertRedirect(route('login'))->assertSessionHasErrors('nexo_sso');
    $this->assertGuest();
});

test('AC-FLOW-3: an id_token for another audience or issuer aborts login', function (): void {
    nexoSsoFakeProvider([
        'access_token' => 'fake', 'token_type' => 'Bearer',
        'id_token' => nexoSsoIdToken(['aud' => 'another-client']),
    ]);
    nexoSsoCallback($this)->assertRedirect(route('login'));
    $this->assertGuest();

    nexoSsoFakeProvider([
        'access_token' => 'fake', 'token_type' => 'Bearer',
        'id_token' => nexoSsoIdToken(['iss' => 'https://evil.test']),
    ]);
    nexoSsoCallback($this)->assertRedirect(route('login'));
    $this->assertGuest();
});
