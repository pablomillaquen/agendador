<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('clients', \App\Http\Controllers\ClientController::class);
Route::apiResource('business-hours', \App\Http\Controllers\BusinessHourController::class);
Route::get('availability', \App\Http\Controllers\AvailabilityController::class);
Route::apiResource('appointments', \App\Http\Controllers\AppointmentController::class);
Route::apiResource('conversations', \App\Http\Controllers\ConversationController::class);
Route::apiResource('messages', \App\Http\Controllers\MessageController::class);
Route::apiResource('blocked-periods', \App\Http\Controllers\BlockedPeriodController::class)->only(['store', 'destroy']);
