<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasRole('project-manager') && $project->manager_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole('project-manager') && $project->manager_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return false;
    }

    public function close(User $user, Project $project): bool
    {
        return $user->hasRole('project-manager') && $project->manager_id === $user->id;
    }
}
