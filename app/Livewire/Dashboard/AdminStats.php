<?php

namespace App\Livewire\Dashboard;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminStats extends Component
{
    public int $refreshTrigger = 0;

    #[On('task-updated')]
    #[On('project-updated')]
    public function onDataUpdated(): void
    {
        $this->refreshTrigger++;
    }

    public function render(): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::whereNotIn('status', ['closed', 'completed'])->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'total_users' => User::count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
            'pending_tasks' => Task::where('status', 'todo')->count(),
            'delayed_tasks' => Task::whereNotNull('deadline')
                ->whereDate('deadline', '<', now()->toDateString())
                ->where('status', '!=', 'done')
                ->count(),

            'delayed_projects' => Project::whereHas('tasks', function($q) {
                $q->whereNotNull('deadline')
                  ->whereDate('deadline', '<', now()->toDateString())
                  ->where('status', '!=', 'done');
            })->whereNotIn('status', ['closed', 'completed'])->count(),

            'last_task' => Task::latest()->first(),
            'last_project' => Project::latest()->first(),
            'last_user' => User::latest()->first(),
            'last_notification' => \Illuminate\Support\Facades\DB::table('notifications')->latest('created_at')->first(),
        ];

        return view('livewire.dashboard.admin-stats', compact('stats'));
    }
}
