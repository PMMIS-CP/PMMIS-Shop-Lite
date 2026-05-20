<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:work --once --max-jobs=10 --time-limit=30')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('livewire:clean-temporary-uploads')
    ->hourly();