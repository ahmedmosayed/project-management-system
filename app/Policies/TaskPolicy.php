<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasRole('project-manager')) {
            return $task->project->manager_id === $user->id;
        }

        return $task->assigned_to === $user->id;
    }

    public function create(User $user, $project): bool
    {
        if ($user->hasRole('project-manager')) {
            return $project->manager_id === $user->id;
        }
        
        return false;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasRole('project-manager') && $task->project->manager_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasRole('project-manager') && $task->project->manager_id === $user->id;
    }

    public function changeStatus(User $user, Task $task): bool
    {
        if ($user->hasRole('project-manager')) {
            return $task->project->manager_id === $user->id;
        }

        return $task->assigned_to === $user->id;
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->changeStatus($user, $task);
    }

    public function attach(User $user, Task $task): bool
    {
        return $this->changeStatus($user, $task);
    }
}
