<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class ProjectReportService
{
    public function generateReport(Project $project, Task $task, User $user): ProjectReport
    {
        $summary = $this->calculateSummary($project);
        $detailed = $this->calculateDetailed($project);

        return ProjectReport::create([
            'project_id' => $project->id,
            'task_id' => $task->id,
            'created_by' => $user->id,
            'generated_at' => now(),
            'completed_at' => now(),
            'summary_data' => $summary,
            'detailed_data' => $detailed,
        ]);
    }

    private function calculateSummary(Project $project): array
    {
        $tasks = $project->tasks()->with('assignee')->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'done')->count();
        $pendingTasks = $tasks->whereIn('status', ['todo', 'in_progress', 'review'])->count();
        $delayedTasks = $tasks->filter(fn ($task) => $task->deadline && $task->deadline->isPast() && $task->status !== 'done')->count();

        $tasksPerUser = $tasks->groupBy('assigned_to')->map(function ($userTasks, $userId) {
            $user = User::find($userId);
            return [
                'user' => $user ? $user->name : 'Unassigned',
                'total' => $userTasks->count(),
                'completed' => $userTasks->where('status', 'done')->count(),
            ];
        })->values();

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'delayed_tasks' => $delayedTasks,
            'tasks_per_user' => $tasksPerUser,
        ];
    }

    private function calculateDetailed(Project $project): array
    {
        $milestones = $project->milestones()->with(['tasks.assignee'])->orderBy('id')->get();

        return $milestones->map(function ($milestone) {
            return [
                'milestone_title' => $milestone->title,
                'milestone_status' => $milestone->status,
                'tasks' => $milestone->tasks->map(function ($task) {
                    return [
                        'title' => $task->title,
                        'assigned_user' => $task->assignee?->name ?? 'Unassigned',
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'start_date' => $task->created_at?->format('Y-m-d'),
                        'end_date' => $task->deadline?->format('Y-m-d'),
                        'completion_date' => $task->completed_at?->format('Y-m-d'),
                        'delayed' => $task->deadline && $task->deadline->isPast() && $task->status !== 'done',
                    ];
                }),
            ];
        })->toArray();
    }
}
