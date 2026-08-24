<?php

use App\Http\Controllers\MonitorController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('ping', 'Ping')->name('ping');
    Route::inertia('http', 'Http')->name('http');
    Route::get('monitors', [MonitorController::class, 'index'])->name('monitors.index');
    Route::get('monitors/create', [MonitorController::class, 'create'])->name('monitors.create');
    Route::post('monitors', [MonitorController::class, 'store'])->name('monitors.store');
});

require __DIR__.'/settings.php';
