<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return 'Login Page Placeholder';
})->name('login');

Route::post('/logout', function () {
    auth()->logout();

    return redirect('/');
})->name('logout');
