<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $ab1 = Building::where('code', 'AB1')->first();

        Room::create([
            'building_id' => $ab1?->id ?? 1,
            'code' => '125',
            'room_type' => 'Lecture Room',
            'capacity' => 50,
            'status' => 'available',
        ]);

        Room::create([
            'building_id' => $ab1?->id ?? 1,
            'code' => '126',
            'room_type' => 'Lecture Room',
            'capacity' => 50,
            'status' => 'available',
        ]);
    }
}
