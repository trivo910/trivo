<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the public home page', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Digital marketing that drives revenue.');
});

it('renders the primary public pages', function () {
    $this->get('/about-us')->assertOk()->assertSee('About us');
    $this->get('/services')->assertOk()->assertSee('Our services');
    $this->get('/contact')->assertOk()->assertSee('Contact');
    $this->get('/thank-you')->assertOk()->assertSee('Thanks for reaching out.');
});
