<?php

use App\Jobs\DispatchHttpCheckPolls;
use App\Jobs\PruneHttpCheckLogs;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new DispatchHttpCheckPolls)
    ->everyMinute()
    ->withoutOverlapping();

Schedule::job(new PruneHttpCheckLogs)
    ->daily()
    ->withoutOverlapping();
