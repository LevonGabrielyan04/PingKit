<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('ping', 'Ping')->name('ping');
    Route::inertia('http', 'Http')->name('http');
});

require __DIR__.'/settings.php';
