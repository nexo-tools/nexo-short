<?php

// Shared support for the NexoSso suite: a throwaway RSA keypair, a fake OIDC
// provider (discovery/JWKS/token) and an id_token signer. require_once'd by
// each test file — functions are guarded for repeat inclusion.

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

if (! function_exists('nexoSsoKeypair')) {
    /** @return array{private: string, jwk: array<string, string>} */
    function nexoSsoKeypair(): array
    {
        static $pair = null;
        if ($pair !== null) {
            return $pair;
        }

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $privatePem);
        $details = openssl_pkey_get_details($key);

        $b64url = fn (string $bin): string => rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');

        return $pair = [
            'private' => $privatePem,
            'jwk' => [
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'kid' => 'nexo-test-key',
                'n' => $b64url($details['rsa']['n']),
                'e' => $b64url($details['rsa']['e']),
            ],
        ];
    }
}

if (! function_exists('nexoSsoIdToken')) {
    /** Signed id_token with sane defaults; override any claim (null removes it). */
    function nexoSsoIdToken(array $overrides = [], ?string $signWithPem = null): string
    {
        $claims = array_merge([
            'iss' => config('nexo-sso.issuer'),
            'aud' => config('nexo-sso.client_id'),
            'sub' => 'user-uuid-0001',
            'exp' => time() + 300,
            'iat' => time(),
            'email' => 'user@example.com',
            'email_verified' => true,
            'name' => 'Test User',
        ], $overrides);
        $claims = array_filter($claims, fn ($value) => $value !== null);

        return JWT::encode($claims, $signWithPem ?? nexoSsoKeypair()['private'], 'RS256', 'nexo-test-key');
    }
}

if (! function_exists('nexoSsoFakeProvider')) {
    /** Fakes discovery + JWKS + token endpoints. Pass the token response body (or a Closure/callable fake). */
    function nexoSsoFakeProvider(?array $tokenResponse = null): void
    {
        $issuer = config('nexo-sso.issuer');
        $tokenResponse ??= ['access_token' => 'fake-access-token', 'token_type' => 'Bearer', 'id_token' => nexoSsoIdToken()];

        Http::fake([
            $issuer.'/.well-known/openid-configuration' => Http::response([
                'issuer' => $issuer,
                'authorization_endpoint' => $issuer.'/oauth/authorize',
                'token_endpoint' => $issuer.'/oauth/token',
                'userinfo_endpoint' => $issuer.'/oauth/userinfo',
                'jwks_uri' => $issuer.'/oauth/jwks',
            ]),
            $issuer.'/oauth/jwks' => Http::response(['keys' => [nexoSsoKeypair()['jwk']]]),
            $issuer.'/oauth/token' => Http::response($tokenResponse),
        ]);
    }
}

if (! function_exists('nexoSsoCallback')) {
    /** Drives the callback with a consistent state/verifier session, as if the provider redirected back. */
    function nexoSsoCallback(TestCase $test, string $code = 'auth-code'): TestResponse
    {
        return $test
            ->withSession(['nexo_sso.state' => str_repeat('s', 40), 'nexo_sso.verifier' => str_repeat('v', 64)])
            ->get(route('nexo-sso.callback', ['code' => $code, 'state' => str_repeat('s', 40)]));
    }
}
