<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
})->purpose('Display an inspiring quote');

Artisan::command('db:seed-custom', function () {
    $this->call('db:seed', ['--class' => 'DatabaseSeeder']);
})->purpose('Seed the database with custom data');

Artisan::command('cache:clear', function () {
    $this->call('cache:clear');
})->purpose('Clear the application cache');

Artisan::command('report:generate', function () {
    // Logic for generating a custom report
    $this->info('Custom report generated successfully.');
})->purpose('Generate a custom report');
