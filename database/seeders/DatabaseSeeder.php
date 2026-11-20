<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CampusSeeder::class,
            CollegeSeeder::class,
            DepartmentSeeder::class,
            BuildingSeeder::class,
            RoomSeeder::class,
            AcademicPeriodSeeder::class,
            FacultySeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Dr. Arlinda N. Ramasola',
            'email' => 'arlinda.ramasola@example.com',
        ]);
    }
}
