<?php

use Illuminate\Foundation\Inspiring;
use App\Events\KFChart;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('bignews', function () {
    broadcast(new KFChart("BIG NEWS!"));
    $this->comment("news sent1111");
})->describe('Send news');