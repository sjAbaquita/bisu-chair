<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        Campus::create([
            'name' => 'Bohol Island State University - Main Campus',
            'code' => 'MC',
            'address' => 'Tagbilaran City, Bohol',
            'is_active' => true,
        ]);

        Campus::create([
            'name' => 'Bohol Island State University - Candijay Campus',
            'code' => 'CAN',
            'address' => 'Cogtong, Candijay, Bohol',
            'is_active' => true,
        ]);
    }
}
