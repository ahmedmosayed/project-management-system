<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $admin->assignRole('admin');

        $pm = User::factory()->create([
            'name' => 'Project Manager',
            'email' => 'pm@example.com',
            'password' => 'password',
        ]);
        $pm->assignRole('project-manager');

        $member = User::factory()->create([
            'name' => 'Team Member',
            'email' => 'member@example.com',
            'password' => 'password',
        ]);
        $member->assignRole('team-member');
    }
}
