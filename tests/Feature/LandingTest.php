<?php

// The landing is the only page a first-time visitor sees. Two things it used to
// get wrong: the h1 repeated the wordmark instead of saying what the tool does,
// and the only way in was "Sign in" even on an instance with open registration.

it('leads with what the tool does, not with its own name', function () {
    $html = $this->get(panelUrl('/'))->assertOk()->getContent();

    expect(preg_match('/<h1[^>]*>\s*'.preg_quote(__('Short links on your own domain'), '/').'\s*<\/h1>/', $html))
        ->toBe(1, 'The h1 is not the value proposition.');
});

it('offers registration on the landing when the instance allows it', function () {
    config()->set('nexo.auth_mode', 'local');
    config()->set('nexo.allow_registration', true);

    $html = $this->get(panelUrl('/'))->assertOk()->getContent();

    expect(str_contains($html, route('register')))
        ->toBeTrue('A visitor has to open the login page to discover they can sign up.');
});

it('does not offer registration when the instance has it closed', function () {
    config()->set('nexo.auth_mode', 'local');
    config()->set('nexo.allow_registration', false);

    $html = $this->get(panelUrl('/'))->assertOk()->getContent();

    expect(str_contains($html, route('register')))->toBeFalse();
    expect(str_contains($html, route('login')))->toBeTrue();
});

it('shows this instance short host instead of describing it', function () {
    $html = $this->get(panelUrl('/'))->assertOk()->getContent();

    expect(str_contains($html, (string) config('nexo.short_host')))
        ->toBeTrue('The landing never shows what a short link from this instance looks like.');
});
