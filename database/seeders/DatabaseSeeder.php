<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Invitation;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Import users from JSON if present; else create a few samples
        $jsonPath = storage_path('app/private/private/users.json');
        $usersData = [];
        if (is_file($jsonPath)) {
            $usersData = json_decode(file_get_contents($jsonPath), true) ?: [];
        }

        if ($usersData) {
            foreach ($usersData as $email => $u) {
                User::updateOrCreate(
                    ['email' => strtolower($email)],
                    [
                        'name' => $u['name'] ?? 'User',
                        'role' => $u['role'] ?? 'student',
                        'avatar' => $u['avatar'] ?? '/profile.png',
                        'password' => $u['password'] ?? Hash::make('password'),
                    ]
                );
            }
        } else {
            // Fallback sample users
            $teacher = User::firstOrCreate(
                ['email' => 'tan@gmail.com'],
                ['name' => 'Tan', 'role' => 'teacher', 'avatar' => '/profile.png', 'password' => Hash::make('password')]
            );
            User::firstOrCreate(
                ['email' => 'john@gmail.com'],
                ['name' => 'John', 'role' => 'student', 'avatar' => '/profile.png', 'password' => Hash::make('password')]
            );
            User::firstOrCreate(
                ['email' => 'bob@gmail.com'],
                ['name' => 'Bob', 'role' => 'student', 'avatar' => '/profile.png', 'password' => Hash::make('password')]
            );
        }

        // Optional: seed sample classrooms only when explicitly enabled
        if (env('SEED_SAMPLE_CLASSES', false)) {
            $teacher = User::where('role', 'teacher')->first();
            $c1 = Classroom::firstOrCreate(['code' => 'MATH01'], ['name' => 'Mathematics 1', 'teacher_id' => $teacher?->id]);
            $c2 = Classroom::firstOrCreate(['code' => 'MATH02'], ['name' => 'Mathematics 2', 'teacher_id' => $teacher?->id]);
            $c3 = Classroom::firstOrCreate(['code' => 'MATH03'], ['name' => 'Mathematics 3', 'teacher_id' => $teacher?->id]);
        }

        // Import enrollments from JSON if present
        $enrollPath = storage_path('app/private/private/enrollments.json');
        if (is_file($enrollPath)) {
            $enrollData = json_decode(file_get_contents($enrollPath), true) ?: [];
            foreach ($enrollData as $email => $classes) {
                $user = User::where('email', strtolower($email))->first();
                if (!$user) continue;
                foreach ((array)$classes as $c) {
                    $class = Classroom::where('code', $c['code'] ?? '')->orWhere('id', $c['id'] ?? 0)->first();
                    if ($class) {
                        Enrollment::firstOrCreate(['user_id' => $user->id, 'classroom_id' => $class->id]);
                    }
                }
            }
        } else if (env('SEED_SAMPLE_CLASSES', false)) {
            // Sample enrollment (only if sample classes are seeded)
            $john = User::where('email', 'john@gmail.com')->first();
            if ($john ?? false) {
                foreach (Classroom::whereIn('code', ['MATH01','MATH02','MATH03'])->pluck('id') as $cid) {
                    Enrollment::firstOrCreate(['user_id' => $john->id, 'classroom_id' => $cid]);
                }
            }
        }

        // Seed default user and example questions
        $this->call([
            DefaultUserSeeder::class,
            QuestionsBankSeeder::class,
        ]);
    }
}
