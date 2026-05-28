<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        Role::findOrCreate(User::ROLE_STUDENT, $guard);
        Role::findOrCreate(User::ROLE_INSTRUCTOR, $guard);
        Role::findOrCreate(User::ROLE_ADMIN, $guard);
        Role::findOrCreate(User::ROLE_SUPER_ADMIN, $guard);
    }
}
