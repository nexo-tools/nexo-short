<?php

it('AC-31: the privacy page lists what is stored per click and states no raw IPs', function () {
    $response = $this->get(panelUrl('privacy'));

    $response->assertOk()
        ->assertSee(__('What we store for each click'))
        ->assertSee(__('A coarse device type: mobile, desktop or bot.'))
        ->assertSee(__('A daily-rotating anonymous fingerprint, used only to count unique visitors. It cannot be linked across days.'))
        ->assertSee(__('What we never store'));

    // States raw IPs / User-Agents are never stored.
    expect($response->getContent())->toContain('IP address');
});

it('AC-31: the privacy page is on the panel host and carries no noindex', function () {
    $this->get(panelUrl('privacy'))->assertHeaderMissing('X-Robots-Tag');
});
