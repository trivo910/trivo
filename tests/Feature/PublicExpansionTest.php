<?php

it('renders portfolio and not-found pages', function () {
    $this->get('/portfolio')->assertOk()->assertSee('Case studies');
    $this->get('/portfolio/travel-brand-growth-sprint')->assertOk()->assertSee('Travel brand growth sprint');
    $this->get('/missing-page')->assertNotFound();
});
