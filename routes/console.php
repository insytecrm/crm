<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('google-sheets:sync')
    ->everyMinute()
    ->withoutOverlapping(5);

Schedule::command('quotations:expire')
    ->daily()
    ->withoutOverlapping();
