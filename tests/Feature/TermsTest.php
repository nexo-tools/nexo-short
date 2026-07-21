<?php

it('AC-39: the terms page is published on the panel host', function () {
    $this->get(panelUrl('terms'))
        ->assertOk()
        ->assertSee(__('Terms of Use'))
        ->assertSee(__('Acceptable use'))
        ->assertHeaderMissing('X-Robots-Tag');
});
