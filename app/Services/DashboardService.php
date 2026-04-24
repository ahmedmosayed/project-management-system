<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @return array{
     *     total_tasks: int,
     *     completed_tasks: int,
     *     delayed_tasks_count: int,
     *     overall_progress: float,
     *     status_counts: array<string, int>,
     *     delayed_tasks: Collection<int, Task>,
     *     project_progress: Collection<int, array{id: int, name: string, total_tasks: int, completed_tasks: int, progress: float}>
     * }
     */
    public function getStats(User $user): array
    {
        $base = $this->visibleTasksQuery($user);

        $total = (clone $base)->count();
        $completed = (clone $base)->where('status', 'done')->count();

        $delayedQuery = (clone $base)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now()->toDateString())
            ->where('status', '!=', 'done');

        $delayedCount = (clone $delayedQuery)->count();

        $statusCounts = [];
        foreach (['todo', 'in_progress', 'review', 'done', 'blocked'] as $status) {
            $statusCounts[$status] = (clone $base)->where('status', $status)->count();
        }

        $overallProgress = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        $delayedTasks = (clone $delayedQuery)
            ->with(['project'])
            ->orderBy('deadline')
            ->limit(15)
            ->get();

        $projectProgress = $this->projectProgressBreakdown($user);

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'delayed_tasks_count' => $delayedCount,
            'overall_progress' => $overallProgress,
            'status_counts' => $statusCounts,
            'delayed_tasks' => $delayedTasks,
            'project_progress' => $projectProgress,
        ];
    }

    /**
     * @return array{
     *     projects: Collection<int, array{name: string, progress: float, total_tasks: int, completed_tasks: int, delayed_tasks: int, high_priority_tasks: int, next_milestone: ?array{name: string, due_date: string}, upcoming_deadlines: Collection<int, Task>, at_risk_tasks: Collection<int, Task>, team_workload: array}
     * }
     */
    public function getBriefing(User $user): array
    {
        // For PM: show only their managed projects
        // For Admin: show all projects
        $projects = Project::query()
            ->when(!$user->hasRole('admin'), fn ($q) => $q->where('manager_id', $user->id))
            ->with(['milestones' => fn ($q) => $q->orderBy('due_date'), 'tasks.assignee'])
            ->get();

        $briefing = $projects->map(function ($project) {
            $tasks = $project->tasks;
            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('status', 'done')->count();
            $delayedTasks = $tasks->filter(fn ($t) => $t->deadline && $t->deadline->isPast() && $t->status !== 'done')->count();
            $highPriorityTasks = $tasks->whereIn('priority', ['high', 'urgent'])->count();
            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

            $nextMilestone = $project->milestones->where('due_date', '>', now())->sortBy('due_date')->first();
            $upcomingDeadlines = $tasks->whereNotNull('deadline')->where('deadline', '>', now())->where('deadline', '<', now()->addDays(7))->sortBy('deadline')->take(5);
            $atRiskTasks = $tasks->filter(fn ($t) => $t->deadline && $t->deadline->isPast() && $t->status !== 'done')->take(5);

            $teamWorkload = $tasks->groupBy('assigned_to')->map(function ($userTasks, $userId) {
                $user = User::find($userId);
                return [
                    'user' => $user ? $user->name : 'Unassigned',
                    'total' => $userTasks->count(),
                    'completed' => $userTasks->where('status', 'done')->count(),
                ];
            })->values();

            return [
                'name' => $project->name,
                'progress' => $progress,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'delayed_tasks' => $delayedTasks,
                'high_priority_tasks' => $highPriorityTasks,
                'next_milestone' => $nextMilestone ? ['name' => $nextMilestone->title, 'due_date' => $nextMilestone->due_date->format('M j, Y')] : null,
                'upcoming_deadlines' => $upcomingDeadlines,
                'at_risk_tasks' => $atRiskTasks,
                'team_workload' => $teamWorkload,
            ];
        });

        return ['projects' => $briefing];
    }

    public function totalTasks(User $user): int
    {
        return $this->visibleTasksQuery($user)->count();
    }

    public function completedTasks(User $user): int
    {
        return $this->visibleTasksQuery($user)->where('status', 'done')->count();
    }

    public function delayedTasksCount(User $user): int
    {
        return $this->visibleTasksQuery($user)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now()->toDateString())
            ->where('status', '!=', 'done')
            ->count();
    }

    public function overallProgressPercent(User $user): float
    {
        $total = $this->totalTasks($user);
        if ($total === 0) {
            return 0.0;
        }

        return round(($this->completedTasks($user) / $total) * 100, 1);
    }

    /**
     * Tasks with deadline before today, not completed.
     *
     * @return Collection<int, Task>
     */
    public function delayedTasks(User $user, int $limit = 15): Collection
    {
        return $this->visibleTasksQuery($user)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now()->toDateString())
            ->where('status', '!=', 'done')
            ->with(['project'])
            ->orderBy('deadline')
            ->limit($limit)
            ->get();
    }

    /**
     * Per-project completion rate using only tasks visible to the user.
     *
     * @return Collection<int, array{id: int, name: string, total_tasks: int, completed_tasks: int, progress: float}>
     */
    public function projectProgressBreakdown(User $user, ?int $maxProjects = 12): Collection
    {
        $projectIds = $this->visibleTasksQuery($user)
            ->distinct()
            ->pluck('project_id')
            ->filter();

        if ($projectIds->isEmpty()) {
            return collect();
        }

        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->orderBy('name')
            ->when($maxProjects, fn ($q) => $q->limit($maxProjects))
            ->get(['id', 'name']);

        return $projects->map(function (Project $project) use ($user) {
            $q = $this->visibleTasksQuery($user)->where('project_id', $project->id);
            $total = (clone $q)->count();
            $done = (clone $q)->where('status', 'done')->count();
            $progress = $total > 0 ? round(($done / $total) * 100, 1) : 0.0;

            return [
                'id' => $project->id,
                'name' => $project->name,
                'total_tasks' => $total,
                'completed_tasks' => $done,
                'progress' => $progress,
            ];
        })->values();
    }

    /**
     * @return Builder<Task>
     */
    private function visibleTasksQuery(User $user): Builder
    {
        $q = Task::query();

        if ($user->hasRole('admin')) {
            return $q;
        }

        if ($user->hasRole('project-manager')) {
            // PM sees only tasks from projects they manage
            return $q->whereHas('project', fn ($pq) => $pq->where('manager_id', $user->id));
        }

        // Team member sees only their assigned tasks
        return $q->where('assigned_to', $user->id);
    }
}
