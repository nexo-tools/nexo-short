<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

function panel(string $path = '/'): string
{
    return 'http://'.config('nexo.panel_host').'/'.ltrim($path, '/');
}

it('lets a user log in with valid credentials', function () {
    $user = User::factory()->create(['password' => Hash::make('secret-password')]);

    $response = $this->post(panel('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('panel'));
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => Hash::make('secret-password')]);

    $this->post(panel('login'), ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('issues an http-only session cookie on login (secure sessions)', function () {
    $user = User::factory()->create(['password' => Hash::make('secret-password')]);

    $response = $this->post(panel('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ]);

    $session = collect($response->headers->getCookies())
        ->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

    expect($session)->not->toBeNull();
    expect($session->isHttpOnly())->toBeTrue();
});

it('AC-13: rate-limits login after too many attempts', function () {
    $user = User::factory()->create(['password' => Hash::make('secret-password')]);

    collect(range(1, 5))->each(function () use ($user) {
        $this->post(panel('login'), ['email' => $user->email, 'password' => 'wrong']);
    });

    $response = $this->post(panel('login'), ['email' => $user->email, 'password' => 'wrong']);

    $response->assertSessionHasErrors('email');
    expect(session('errors')->get('email')[0])->toContain('Too many');
});

it('logs a user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(panel('logout'))->assertRedirect(route('landing'));

    $this->assertGuest();
});

it('registers a new user when registration is open', function () {
    config(['nexo.allow_registration' => true]);

    $response = $this->post(panel('register'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'secret-password-1',
        'password_confirmation' => 'secret-password-1',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('panel'));
    expect(User::where('email', 'ada@example.com')->exists())->toBeTrue();
});

it('AC-14: hides the registration surface when NEXO_ALLOW_REGISTRATION is false', function () {
    config(['nexo.allow_registration' => false]);

    $this->get(panel('register'))->assertNotFound();
    $this->post(panel('register'), [
        'name' => 'Blocked',
        'email' => 'blocked@example.com',
        'password' => 'secret-password-1',
        'password_confirmation' => 'secret-password-1',
    ])->assertNotFound();

    expect(User::where('email', 'blocked@example.com')->exists())->toBeFalse();
});
