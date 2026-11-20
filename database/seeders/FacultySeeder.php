<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $hmDepartment = Department::where('code', 'HM')->first();

        $arlinda = Faculty::create([
            'first_name' => 'Arlinda',
            'middle_name' => 'N',
            'last_name' => 'Ramasola',
            'email' => 'arlinda.ramasola@example.com',
            'status' => 'active',
            'highest_degree_earned' => 'Doctor of Philosophy in Human Resource Management',
            'specialization' => 'Hospitality Management',
        ]);

        if ($hmDepartment) {
            DB::table('faculty_department')->insert([
                'faculty_id' => $arlinda->id,
                'department_id' => $hmDepartment->id,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
