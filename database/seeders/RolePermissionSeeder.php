<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage-users',
            'manage-projects',
            'manage-tasks',
            'view-tasks',
            'comment-tasks',
            'upload-task-attachments',
            'view-reports',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $projectManager = Role::firstOrCreate(['name' => 'project-manager', 'guard_name' => 'web']);
        $teamMember = Role::firstOrCreate(['name' => 'team-member', 'guard_name' => 'web']);

        $admin->syncPermissions(Permission::all());

        $projectManager->syncPermissions([
            'manage-projects',
            'manage-tasks',
            'view-tasks',
            'comment-tasks',
            'upload-task-attachments',
            'view-reports',
        ]);

        $teamMember->syncPermissions([
            'view-tasks',
            'comment-tasks',
            'upload-task-attachments',
        ]);
    }
}
