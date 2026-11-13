<?php

it('renders the core public and legal pages', function () {
    $this->get('/blog')->assertOk()->assertSee('Fresh thinking');
    $this->get('/blog/turn-intent-into-pipeline-seo-led-content')->assertOk()->assertSee('How to turn intent into pipeline with SEO-led content');
    $this->get('/privacy-policy')->assertOk()->assertSee('Privacy policy');
    $this->get('/terms-conditions')->assertOk()->assertSee('Terms')->assertSee('conditions');
    $this->get('/return-refund-policy')->assertOk()->assertSee('Return')->assertSee('refund');
});
