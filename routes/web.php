<?php

use App\Http\Controllers\Admin\AcademicPeriodController;
use App\Http\Controllers\Admin\BuildingController;
use App\Http\Controllers\Admin\CampusController;
use App\Http\Controllers\Admin\CollegeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\RoomController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('campuses', CampusController::class);
        Route::resource('colleges', CollegeController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('buildings', BuildingController::class);
        Route::resource('rooms', RoomController::class);
        Route::resource('academic_periods', AcademicPeriodController::class);
        Route::resource('faculty', FacultyController::class);
    });
});

require __DIR__.'/settings.php';
