<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use Illuminate\Database\Seeder;

class AcademicPeriodSeeder extends Seeder
{
    public function run(): void
    {
        AcademicPeriod::create([
            'academic_year' => '2026-2027',
            'term' => 'Second Semester',
            'start_date' => '2027-01-08',
            'end_date' => '2027-05-20',
            'status' => 'open_for_encoding',
            'is_current' => true,
        ]);
    }
}
