<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $cbm = College::where('code', 'CBM')->first();

        Department::create([
            'college_id' => $cbm?->id ?? 1,
            'name' => 'Hospitality Management Department',
            'code' => 'HM',
            'is_active' => true,
        ]);
    }
}
