<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

it('limits enquiry submissions to three per minute', function () {
    Cache::flush();
    Route::middleware('throttle:leads')->post('/enquiry-probe', fn () => response('ok'));
    foreach (range(1, 3) as $attempt) {
        $this->post('/enquiry-probe')->assertOk();
    }
    $this->post('/enquiry-probe')->assertStatus(429);
});
