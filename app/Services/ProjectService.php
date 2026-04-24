<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ProjectCompletedNotification;

class ProjectService
{
    public function __construct(private ActivityLogger $activityLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Project $project, array $data): Project
    {
        $wasNotCompleted = $project->status !== ProjectStatus::Completed;

        $project->update($data);

        $project = $project->fresh();

        if ($wasNotCompleted && $project->status === ProjectStatus::Completed) {
            $this->finalizeCompletedProject($project);
        }

        return $project->fresh();
    }

    private function finalizeCompletedProject(Project $project): void
    {
        $completedAt = now();
        $anchor = $project->start_date ?? $project->created_at;
        $durationDays = $anchor ? max(0, (int) ceil($anchor->diffInDays($completedAt))) : null;

        $total = $project->tasks()->count();
        $done = $project->tasks()->where('status', 'done')->count();
        $pct = $total > 0 ? round(($done / $total) * 100, 1) : 0.0;

        $lines = [
            __('Calendar duration from project start: :days days.', ['days' => $durationDays ?? '—']),
            __('Tasks marked done: :done of :total (:pct%).', ['done' => $done, 'total' => $total, 'pct' => $pct]),
        ];
        if ($project->budget !== null) {
            $lines[] = __('Budget on record: :b.', ['b' => (string) $project->budget]);
        }
        $lines[] = __('Completion recorded at :time.', ['time' => $completedAt->toDateTimeString()]);

        $notes = implode("\n", $lines);

        $project->forceFill([
            'completed_at' => $completedAt,
            'closure_duration_days' => $durationDays,
            'closure_performance_notes' => $notes,
        ])->save();

        $this->activityLogger->log(
            'project.closure',
            __('Project marked completed: :name', ['name' => $project->name]),
            $project->fresh(),
            [
                'duration_days' => $durationDays,
                'tasks_done' => $done,
                'tasks_total' => $total,
            ]
        );

        $this->notifyProjectCompleted($project->fresh());
    }

    private function notifyProjectCompleted(Project $project): void
    {
        $project->loadMissing('manager');

        $assigneeIds = Task::query()
            ->where('project_id', $project->id)
            ->whereNotNull('assigned_to')
            ->distinct()
            ->pluck('assigned_to');

        $users = User::query()->whereIn('id', $assigneeIds)->get();

        if ($project->manager) {
            $users = $users->push($project->manager);
        }

        foreach ($users->unique('id')->values() as $user) {
            $user->notify(new ProjectCompletedNotification($project));
        }
    }

    public function assignManager(Project $project, User|int $manager): Project
    {
        $managerId = $manager instanceof User ? $manager->getKey() : $manager;

        $project->forceFill(['manager_id' => $managerId])->save();

        return $project->fresh();
    }

    public function closeProject(Project $project): Project
    {
        $project->update([
            'status' => ProjectStatus::Closed,
        ]);

        return $project->fresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
