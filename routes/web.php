<?php

use App\Http\Controllers\Admin\AcademicPeriodController;
use App\Http\Controllers\Admin\BuildingController;
use App\Http\Controllers\Admin\CampusController;
use App\Http\Controllers\Admin\ClassMeetingController;
use App\Http\Controllers\Admin\ClassOfferingController;
use App\Http\Controllers\Admin\ClassOfferingFacultyController;
use App\Http\Controllers\Admin\CollegeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubjectController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('campuses', CampusController::class);
        Route::resource('colleges', CollegeController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('sections', SectionController::class);
        Route::resource('buildings', BuildingController::class);
        Route::resource('rooms', RoomController::class);
        Route::get('room-utilization', [RoomController::class, 'indexUtilization'])->name('room-utilization.index');
        Route::resource('academic_periods', AcademicPeriodController::class);
        Route::resource('faculty', FacultyController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('class_offerings', ClassOfferingController::class);

        // Faculty assignment routes for class offerings
        Route::get('class_offerings/{class_offering}/faculty', [ClassOfferingFacultyController::class, 'index'])
            ->name('class_offerings.faculty.index');
        Route::post('class_offerings/{class_offering}/faculty', [ClassOfferingFacultyController::class, 'store'])
            ->name('class_offerings.faculty.store');
        Route::put('class_offerings/{class_offering}/faculty/{facultyAssignment}', [ClassOfferingFacultyController::class, 'update'])
            ->name('class_offerings.faculty.update');
        Route::delete('class_offerings/{class_offering}/faculty/{facultyAssignment}', [ClassOfferingFacultyController::class, 'destroy'])
            ->name('class_offerings.faculty.destroy');

        // Class meeting routes for class offerings
        Route::get('class_offerings/{class_offering}/meetings', [ClassMeetingController::class, 'index'])
            ->name('class_offerings.meetings.index');
        Route::get('class_offerings/{class_offering}/meetings/create', [ClassMeetingController::class, 'create'])
            ->name('class_offerings.meetings.create');
        Route::post('class_offerings/{class_offering}/meetings', [ClassMeetingController::class, 'store'])
            ->name('class_offerings.meetings.store');
        Route::get('class_meetings/{class_meeting}', [ClassMeetingController::class, 'showOccurrences'])
            ->name('class_meetings.show');
        Route::get('class_meetings/{class_meeting}/edit', [ClassMeetingController::class, 'edit'])
            ->name('class_meetings.edit');
        Route::put('class_meetings/{class_meeting}', [ClassMeetingController::class, 'update'])
            ->name('class_meetings.update');
        Route::delete('class_meetings/{class_meeting}', [ClassMeetingController::class, 'destroy'])
            ->name('class_meetings.destroy');
        Route::post('class_offerings/{class_offering}/meetings/one-off', [ClassMeetingController::class, 'generateOneOffOccurrences'])
            ->name('class_offerings.meetings.one-off.store');
    });
});

require __DIR__.'/settings.php';
