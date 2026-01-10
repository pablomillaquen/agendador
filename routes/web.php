<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', \App\Http\Controllers\Admin\ClientController::class)->names('admin.clients');
    Route::get('/calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('calendar.index');
    
    Route::get('/business-hours', [\App\Http\Controllers\Admin\BusinessHourController::class, 'index'])->name('admin.business-hours.index');
    Route::post('/business-hours', [\App\Http\Controllers\Admin\BusinessHourController::class, 'update'])->name('admin.business-hours.update');
});

require __DIR__.'/auth.php';
