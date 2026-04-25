<?php

namespace App\Livewire\Admin;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class Stats extends Component
{
    public function render(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::whereNotIn('status', ['closed', 'completed'])->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
            'pending_tasks' => Task::where('status', 'todo')->count(),
            'delayed_tasks' => Task::whereNotNull('deadline')
                ->whereDate('deadline', '<', now()->toDateString())
                ->where('status', '!=', 'done')
                ->count(),
                
            'users_by_role' => Role::withCount('users')->get()->pluck('users_count', 'name')->toArray(),
        ];

        return view('livewire.admin.stats', compact('stats'));
    }
}
