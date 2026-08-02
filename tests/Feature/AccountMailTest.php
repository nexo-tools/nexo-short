<?php

use App\Mail\LinkReported;
use App\Mail\NexoIdLinked;
use App\Mail\PasswordChanged;
use App\Models\User;
use App\Notifications\ResetPasswordQueued;
use App\Services\NexoSso\NexoSsoUserResolver;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

/** This tool sent no mail at all until 2026-08-02. */
it('AC-MAIL-1: sends a reset link for a local account that forgot its password', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'ana@example.com']);

    $this->post(panelUrl('/forgot-password'), ['email' => 'ana@example.com'])->assertRedirect();

    // Before this, a self-hosted instance in local mode had no way back from a
    // forgotten password except the operator editing the database.
    Notification::assertSentTo($user, ResetPasswordQueued::class);
});

it('AC-MAIL-2: answers the same for an address with no account', function () {
    Notification::fake();

    $this->post(panelUrl('/forgot-password'), ['email' => 'nobody@example.com'])
        ->assertRedirect()
        ->assertSessionHas('status');

    // The reply must not tell a stranger which emails have accounts.
    Notification::assertNothingSent();
});

it('AC-MAIL-3: tells the owner when the password is actually changed', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'ana@example.com']);
    $token = Password::createToken($user);

    $this->post(panelUrl('/reset-password'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'a-brand-new-password',
        'password_confirmation' => 'a-brand-new-password',
    ])->assertRedirect();

    Mail::assertQueued(PasswordChanged::class, fn (PasswordChanged $mail): bool => $mail->hasTo('ana@example.com'));
});

it('AC-MAIL-4: mails the operator when a link is reported, and tells the reporter nothing', function () {
    Mail::fake();

    $response = $this->post(shortUrl('/report'), [
        'slug' => 'abc123',
        'reason' => 'spam',
        'note' => 'Sends people to a fake shop.',
    ]);

    $response->assertOk();

    // The report channel stays anonymous: the mail goes to the operator with
    // the command they will want next, never back to whoever reported.
    Mail::assertQueued(LinkReported::class, fn (LinkReported $mail): bool => $mail->hasTo(config('nexo.support_email')));
});

it('AC-MAIL-5: tells the owner the first time Nexo ID is linked, and only then', function () {
    Mail::fake();

    User::factory()->create(['email' => 'ana@example.com']);
    $claims = ['sub' => 'sub-1', 'email' => 'ana@example.com', 'email_verified' => true, 'name' => 'Ana'];

    app(NexoSsoUserResolver::class)->resolve($claims);
    app(NexoSsoUserResolver::class)->resolve($claims);

    Mail::assertQueued(NexoIdLinked::class, fn (NexoIdLinked $mail): bool => $mail->hasTo('ana@example.com'));
    Mail::assertQueuedCount(1);
});
