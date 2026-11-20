<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\College;
use Illuminate\Database\Seeder;

class CollegeSeeder extends Seeder
{
    public function run(): void
    {
        $candijay = Campus::where('code', 'CAN')->first();

        College::create([
            'campus_id' => $candijay?->id ?? 1,
            'name' => 'College of Business and Management',
            'code' => 'CBM',
            'is_active' => true,
        ]);
    }
}
