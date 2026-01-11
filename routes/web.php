<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
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

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    
    Route::get('/reports/appointments', [\App\Http\Controllers\Admin\ReportingController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/pdf/daily', [\App\Http\Controllers\Admin\ReportingController::class, 'dailyPdf'])->name('admin.reports.daily-pdf');
    Route::get('/reports/pdf/weekly', [\App\Http\Controllers\Admin\ReportingController::class, 'weeklyPdf'])->name('admin.reports.weekly-pdf');
    Route::get('/reports/pdf/filtered', [\App\Http\Controllers\Admin\ReportingController::class, 'filteredPdf'])->name('admin.reports.filtered-pdf');

    Route::get('/conversations/{sessionId}', [\App\Http\Controllers\ConversationController::class, 'show'])
        ->name('conversations.show')
        ->middleware('auth');

    Route::post('/appointments', [\App\Http\Controllers\Admin\AppointmentController::class, 'store'])->name('admin.appointments.store');
});

require __DIR__.'/auth.php';
