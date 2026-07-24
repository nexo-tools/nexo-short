<?php

// Guardian: the help center is served on the PANEL host and translatable, and the
// theme-init + brand layer are wired into the panel shell (light/dark everywhere).
// Both routes are hit on the panel host explicitly — the short host must not serve
// them (its {slug} catch-all 404s /help; see HostChromeIsolationTest).

it('serves a translatable help center on the panel host', function () {
    $this->get(panelUrl('/help'))
        ->assertOk()
        ->assertSee(__('nexo.help.title'));
});

it('stamps the theme before paint and ships the brand layer on the panel host', function () {
    $html = $this->get(panelUrl('/'))->assertOk()->getContent();

    // The FOUC-free theme init sets <html data-theme> ...
    expect($html)->toContain('data-theme');
    // ... and the brand layer is wired into the shell: either the token stylesheet
    // (inline --nexo-*, or the compiled Vite link) or the token-styled chrome
    // (nexo-header/nexo-footer), so it holds with or without a built frontend.
    expect($html)->toMatch('#--nexo-|nexo-brand|tokens\.css|app\.css|/build/assets/app-|nexo-header|nexo-footer#');
});
