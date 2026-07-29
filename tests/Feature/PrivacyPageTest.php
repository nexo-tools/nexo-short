<?php

// The page content moved to lang/<locale>/legal.php (nexo-ui static pages
// standard), so the AC is asserted against that source instead of a Blade view.

it('AC-31: the privacy page renders every section of the legal content', function () {
    $legal = require lang_path(app()->getLocale().'/legal.php');

    $html = $this->get(panelUrl('privacidad'))->assertOk()->getContent();

    expect($html)->toContain($legal['privacy']['title']);

    foreach ($legal['privacy']['sections'] as $section) {
        expect($html)->toContain(e($section['h']));
    }
});

it('AC-31: the privacy page states what a click stores and that raw IPs are not stored', function () {
    $html = $this->get(panelUrl('privacidad'))->assertOk()->getContent();

    // The cookieless click record and the no-raw-IP promise are the two claims
    // the product is built on; they may not silently drop out of the text.
    expect($html)->toContain('device type')
        ->and($html)->toContain('fingerprint')
        ->and($html)->toContain('IP address');
});

it('AC-31: the privacy page is on the panel host and carries no noindex', function () {
    $this->get(panelUrl('privacidad'))->assertHeaderMissing('X-Robots-Tag');
});

it('keeps the old English path working with a permanent redirect', function () {
    $this->get(panelUrl('privacy'))
        ->assertStatus(301)
        ->assertRedirect(route('legal.privacy'));
});
