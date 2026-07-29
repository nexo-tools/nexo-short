<?php

// The page content moved to lang/<locale>/legal.php (nexo-ui static pages
// standard), so the AC is asserted against that source instead of a Blade view.

it('AC-39: the terms page is published on the panel host and renders its sections', function () {
    $legal = require lang_path(app()->getLocale().'/legal.php');

    $html = $this->get(panelUrl('terminos'))
        ->assertOk()
        ->assertHeaderMissing('X-Robots-Tag')
        ->getContent();

    expect($html)->toContain($legal['terms']['title']);

    foreach ($legal['terms']['sections'] as $section) {
        expect($html)->toContain(e($section['h']));
    }
});

it('AC-39: the terms state the abuse rules and that short links are public by nature', function () {
    $html = $this->get(panelUrl('terminos'))->assertOk()->getContent();

    expect($html)->toContain('Misuse')
        ->and($html)->toContain('Reports and moderation')
        ->and($html)->toContain('public by nature');
});

it('keeps the old English path working with a permanent redirect', function () {
    $this->get(panelUrl('terms'))
        ->assertStatus(301)
        ->assertRedirect(route('legal.terms'));
});
