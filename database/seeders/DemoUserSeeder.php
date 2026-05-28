<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $superAdmin = User::firstOrCreate(
            ['email' => 'super@elearning.local'],
            ['name' => 'Super Admin', 'password' => $password]
        );
        $superAdmin->syncRoles([User::ROLE_SUPER_ADMIN]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@elearning.local'],
            ['name' => 'Platform Admin', 'password' => $password]
        );
        $admin->syncRoles([User::ROLE_ADMIN]);

        $instructor = User::firstOrCreate(
            ['email' => 'instructor@elearning.local'],
            ['name' => 'Demo Instructor', 'password' => $password]
        );
        $instructor->syncRoles([User::ROLE_INSTRUCTOR]);

        $student = User::firstOrCreate(
            ['email' => 'student@elearning.local'],
            ['name' => 'Demo Student', 'password' => $password]
        );
        $student->syncRoles([User::ROLE_STUDENT]);
    }
}
