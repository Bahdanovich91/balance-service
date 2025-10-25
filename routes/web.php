<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/health');
});

Route::get('/health', function () {
    return response('OK', 200);
});
