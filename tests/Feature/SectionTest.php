<?php

use App\Models\AcademicPeriod;
use App\Models\Department;
use App\Models\Section;
use App\Models\User;

test('authenticated user can view sections list', function () {
    $user = User::factory()->create();
    $college = \App\Models\College::factory()->create();
    $department = Department::factory()->create(['college_id' => $college->id]);
    $academicPeriod = AcademicPeriod::factory()->create(['status' => 'open']);
    Section::factory()->create([
        'name' => 'BSIT 2A',
        'department_id' => $department->id,
        'academic_period_id' => $academicPeriod->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.sections.index'));

    $response->assertStatus(200);
});

test('authenticated user can view sections list with academic period filter', function () {
    $user = User::factory()->create();
    $college = \App\Models\College::factory()->create();
    $department = Department::factory()->create(['college_id' => $college->id]);
    $period1 = AcademicPeriod::factory()->create(['status' => 'open']);
    $period2 = AcademicPeriod::factory()->create(['status' => 'open']);

    Section::factory()->create([
        'department_id' => $department->id,
        'academic_period_id' => $period1->id,
    ]);
    Section::factory()->create([
        'department_id' => $department->id,
        'academic_period_id' => $period2->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.sections.index', ['academic_period_id' => $period1->id]));

    $response->assertStatus(200);
});

test('authenticated user can create a section', function () {
    $user = User::factory()->create();
    $college = \App\Models\College::factory()->create();
    $department = Department::factory()->create(['college_id' => $college->id]);
    $academicPeriod = AcademicPeriod::factory()->create(['status' => 'open']);

    $response = $this->actingAs($user)->post(route('admin.sections.store'), [
        'department_id' => $department->id,
        'academic_period_id' => $academicPeriod->id,
        'program_name' => 'Bachelor of Science in Information Technology',
        'year_level' => '2nd Year',
        'name' => 'BSIT 2A',
        'expected_enrollment' => 40,
    ]);

    $response->assertRedirect(route('admin.sections.index'));
    $this->assertDatabaseHas('sections', ['name' => 'BSIT 2A']);
});

test('authenticated user can update a section', function () {
    $user = User::factory()->create();
    $college = \App\Models\College::factory()->create();
    $department = Department::factory()->create(['college_id' => $college->id]);
    $academicPeriod = AcademicPeriod::factory()->create(['status' => 'open']);
    $section = Section::factory()->create([
        'department_id' => $department->id,
        'academic_period_id' => $academicPeriod->id,
    ]);

    $response = $this->actingAs($user)->put(route('admin.sections.update', $section), [
        'department_id' => $department->id,
        'academic_period_id' => $academicPeriod->id,
        'program_name' => 'Bachelor of Science in Computer Science',
        'year_level' => '3rd Year',
        'name' => 'BSCS 3B',
        'expected_enrollment' => 35,
    ]);

    $response->assertRedirect(route('admin.sections.index'));
    $this->assertDatabaseHas('sections', ['id' => $section->id, 'name' => 'BSCS 3B']);
});

test('authenticated user can delete a section', function () {
    $user = User::factory()->create();
    $section = Section::factory()->create();

    $response = $this->actingAs($user)->delete(route('admin.sections.destroy', $section));

    $response->assertRedirect(route('admin.sections.index'));
    $this->assertDatabaseMissing('sections', ['id' => $section->id]);
});

test('section name is required', function () {
    $user = User::factory()->create();
    $college = \App\Models\College::factory()->create();
    $department = Department::factory()->create(['college_id' => $college->id]);
    $academicPeriod = AcademicPeriod::factory()->create(['status' => 'open']);

    $response = $this->actingAs($user)->post(route('admin.sections.store'), [
        'department_id' => $department->id,
        'academic_period_id' => $academicPeriod->id,
        'program_name' => 'IT Program',
        'year_level' => '2nd Year',
        'name' => '',
        'expected_enrollment' => 40,
    ]);

    $response->assertSessionHasErrors('name');
});

test('section department must exist', function () {
    $user = User::factory()->create();
    $academicPeriod = AcademicPeriod::factory()->create(['status' => 'open']);

    $response = $this->actingAs($user)->post(route('admin.sections.store'), [
        'department_id' => 99999,
        'academic_period_id' => $academicPeriod->id,
        'program_name' => 'IT Program',
        'year_level' => '2nd Year',
        'name' => 'BSIT 2A',
        'expected_enrollment' => 40,
    ]);

    $response->assertSessionHasErrors('department_id');
});

test('section academic period must exist', function () {
    $user = User::factory()->create();
    $college = \App\Models\College::factory()->create();
    $department = Department::factory()->create(['college_id' => $college->id]);

    $response = $this->actingAs($user)->post(route('admin.sections.store'), [
        'department_id' => $department->id,
        'academic_period_id' => 99999,
        'program_name' => 'IT Program',
        'year_level' => '2nd Year',
        'name' => 'BSIT 2A',
        'expected_enrollment' => 40,
    ]);

    $response->assertSessionHasErrors('academic_period_id');
});

test('section expected enrollment must be at least 0', function () {
    $user = User::factory()->create();
    $college = \App\Models\College::factory()->create();
    $department = Department::factory()->create(['college_id' => $college->id]);
    $academicPeriod = AcademicPeriod::factory()->create(['status' => 'open']);

    $response = $this->actingAs($user)->post(route('admin.sections.store'), [
        'department_id' => $department->id,
        'academic_period_id' => $academicPeriod->id,
        'program_name' => 'IT Program',
        'year_level' => '2nd Year',
        'name' => 'BSIT 2A',
        'expected_enrollment' => -5,
    ]);

    $response->assertSessionHasErrors('expected_enrollment');
});

test('unauthenticated user cannot access sections', function () {
    $response = $this->get(route('admin.sections.index'));
    $response->assertRedirect('/login');
});