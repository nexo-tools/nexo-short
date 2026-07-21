<?php

it('boots and serves the health check', function () {
    $this->get('/up')->assertOk();
});
