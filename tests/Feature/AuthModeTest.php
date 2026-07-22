<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('AC-41: SSO-only mode hides the local login form but keeps the SSO button', function () {
    config(['nexo.auth_mode' => 'sso', 'nexo-sso.enabled' => true]);

    $this->get(panelUrl('login'))
        ->assertOk()
        ->assertSee(__('Continue with Nexo ID'))
        ->assertDontSee('name="password"', false);
});

it('AC-41: SSO-only mode closes the local login POST', function () {
    config(['nexo.auth_mode' => 'sso']);
    $user = User::factory()->create(['password' => Hash::make('secret-password')]);

    $this->post(panelUrl('login'), ['email' => $user->email, 'password' => 'secret-password'])
        ->assertNotFound();
    $this->assertGuest();
});

it('AC-42: SSO-only mode closes local registration', function () {
    config(['nexo.auth_mode' => 'sso']);

    $this->get(panelUrl('register'))->assertNotFound();
    $this->post(panelUrl('register'), [
        'name' => 'X', 'email' => 'x@example.com',
        'password' => 'secret-password-1', 'password_confirmation' => 'secret-password-1',
    ])->assertNotFound();

    expect(User::where('email', 'x@example.com')->exists())->toBeFalse();
});

it('AC-43: local mode shows the local form and local login works', function () {
    config(['nexo.auth_mode' => 'local']);
    $user = User::factory()->create(['password' => Hash::make('secret-password')]);

    $this->get(panelUrl('login'))->assertOk()->assertSee('name="password"', false);

    $this->post(panelUrl('login'), ['email' => $user->email, 'password' => 'secret-password'])
        ->assertRedirect(route('panel'));
    $this->assertAuthenticatedAs($user);
});

it('AC-43: both mode shows the local form and the SSO button together', function () {
    config(['nexo.auth_mode' => 'both', 'nexo-sso.enabled' => true]);

    $this->get(panelUrl('login'))
        ->assertOk()
        ->assertSee('name="password"', false)
        ->assertSee(__('Continue with Nexo ID'));
});
