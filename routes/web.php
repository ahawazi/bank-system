<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return ['Laravel' => app()->version()];
    Artisan::call("add:user");
});
