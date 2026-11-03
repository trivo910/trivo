<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work database --stop-when-empty --max-time=50 --max-jobs=50 --tries=1 --timeout=30')
    ->everyMinute()->withoutOverlapping(2);
