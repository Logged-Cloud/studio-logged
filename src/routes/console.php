<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:prune-demo-pages')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
