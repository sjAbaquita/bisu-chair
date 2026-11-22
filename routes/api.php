<?php

use App\Http\Controllers\Api\SchedulingConflictController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'throttle:60,1'])->group(function () {
    // Conflict checking endpoint for preview
    Route::post('/scheduling/conflicts/check', [SchedulingConflictController::class, 'checkConflicts'])
        ->name('api.scheduling.conflicts.check');

    // Room availability endpoint
    Route::get('/rooms/{room}/availability', [SchedulingConflictController::class, 'getRoomAvailability'])
        ->name('api.rooms.availability');

    // Faculty availability endpoint
    Route::get('/faculty/{faculty}/availability', [SchedulingConflictController::class, 'getFacultyAvailability'])
        ->name('api.faculty.availability');

    // Room capacity check endpoint
    Route::get('/rooms/{room}/capacity-check', [SchedulingConflictController::class, 'checkRoomCapacity'])
        ->name('api.rooms.capacity-check');
});