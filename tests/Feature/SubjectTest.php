<?php

use App\Models\Subject;
use App\Models\User;

test('authenticated user can view subjects list', function () {
    $user = User::factory()->create();
    Subject::factory()->create(['code' => 'CS-101', 'title' => 'Introduction to Computer Science']);

    $response = $this->actingAs($user)->get(route('admin.subjects.index'));

    $response->assertStatus(200);
});

test('authenticated user can create a subject', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.subjects.store'), [
        'code' => 'CS-101',
        'title' => 'Introduction to Computer Science',
        'units' => 3.00,
        'lecture_hours' => 3,
        'laboratory_hours' => 0,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.subjects.index'));
    $this->assertDatabaseHas('subjects', ['code' => 'CS-101']);
});

test('authenticated user can update a subject', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['code' => 'CS-101', 'title' => 'Original Title']);

    $response = $this->actingAs($user)->put(route('admin.subjects.update', $subject), [
        'code' => 'CS-101',
        'title' => 'Updated Title',
        'units' => 3.00,
        'lecture_hours' => 3,
        'laboratory_hours' => 1,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.subjects.index'));
    $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'title' => 'Updated Title']);
});

test('authenticated user can delete a subject', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->delete(route('admin.subjects.destroy', $subject));

    $response->assertRedirect(route('admin.subjects.index'));
    $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
});

test('subject code must be unique', function () {
    $user = User::factory()->create();
    Subject::factory()->create(['code' => 'CS-101']);

    $response = $this->actingAs($user)->post(route('admin.subjects.store'), [
        'code' => 'CS-101',
        'title' => 'Duplicate Code',
        'units' => 3.00,
        'lecture_hours' => 3,
        'laboratory_hours' => 0,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('code');
});

test('subject requires valid code', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.subjects.store'), [
        'code' => '',
        'title' => 'Test Subject',
        'units' => 3.00,
        'lecture_hours' => 3,
        'laboratory_hours' => 0,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('code');
});

test('subject requires valid title', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.subjects.store'), [
        'code' => 'TEST-101',
        'title' => '',
        'units' => 3.00,
        'lecture_hours' => 3,
        'laboratory_hours' => 0,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('title');
});

test('subject units must be valid decimal', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.subjects.store'), [
        'code' => 'TEST-101',
        'title' => 'Test Subject',
        'units' => -1,
        'lecture_hours' => 3,
        'laboratory_hours' => 0,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('units');
});

test('subject laboratory hours must be at least 0', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.subjects.store'), [
        'code' => 'TEST-101',
        'title' => 'Test Subject',
        'units' => 3.00,
        'lecture_hours' => 3,
        'laboratory_hours' => -1,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('laboratory_hours');
});