<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work database --stop-when-empty --max-time=50 --max-jobs=50 --tries=1 --timeout=30')
    ->everyMinute()->withoutOverlapping(2);
Schedule::command('trivo:publish-pages --limit=50')->everyMinute()->withoutOverlapping(5);
Schedule::command('trivo:publish-services --limit=50')->everyMinute()->withoutOverlapping(5);
Schedule::command('trivo:publish-portfolio --limit=50')->everyMinute()->withoutOverlapping(5);
Schedule::command('trivo:publish-posts --limit=50')->everyMinute()->withoutOverlapping(5);
