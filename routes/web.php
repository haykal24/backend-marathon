<?php

use Illuminate\Support\Facades\Route;

// Redirect root path to Filament admin login
Route::get('/', function () {
    return redirect('/admin/login');
});
