<?php

use App\Http\Controllers\HttpCheckLogController;
use App\Http\Controllers\MonitorController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('ping', 'Ping')->name('ping');
    Route::get('http', [HttpCheckLogController::class, 'index'])->name('http');
    Route::get('monitors', [MonitorController::class, 'index'])->name('monitors.index');
    Route::get('monitors/create', [MonitorController::class, 'create'])->name('monitors.create');
    Route::post('monitors', [MonitorController::class, 'store'])->name('monitors.store');
    Route::get('monitors/{monitor}/edit', [MonitorController::class, 'edit'])->name('monitors.edit');
    Route::patch('monitors/{monitor}', [MonitorController::class, 'update'])->name('monitors.update');
});

require __DIR__.'/settings.php';
