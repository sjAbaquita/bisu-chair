<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Building;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        $candijay = Campus::where('code', 'CAN')->first();

        Building::create([
            'campus_id' => $candijay?->id ?? 1,
            'name' => 'Academic Building 1',
            'code' => 'AB1',
            'is_active' => true,
        ]);
    }
}
