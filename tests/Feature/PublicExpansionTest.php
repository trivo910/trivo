<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders portfolio and not-found pages', function () {
    $this->get('/portfolio')->assertOk()->assertSee('Portfolio projects');
    $this->get('/portfolio/travel-brand-growth-sprint')->assertOk()->assertSee('Travel brand growth sprint');
    $this->get('/missing-page')->assertNotFound();
});
