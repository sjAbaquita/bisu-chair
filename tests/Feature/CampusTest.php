<?php

use App\Models\Campus;
use App\Models\User;

test('authenticated user can view campuses list', function () {
    $user = User::factory()->create();
    Campus::factory()->create(['name' => 'Tagbilaran Campus', 'code' => 'TAG']);

    $response = $this->actingAs($user)->get(route('admin.campuses.index'));

    $response->assertStatus(200);
});

test('authenticated user can create a campus', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.campuses.store'), [
        'name' => 'Clarin Campus',
        'code' => 'CLA',
        'address' => 'Clarin, Bohol',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.campuses.index'));
    $this->assertDatabaseHas('campuses', ['code' => 'CLA']);
});
