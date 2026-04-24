<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(private ActivityLogger $activityLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createTask(Project $project, array $data): Task
    {
        return $this->create($project, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Project $project, array $data): Task
    {
        $data['project_id'] = $project->id;
        $this->assertParentConsistency($project, $data, null);
        $data['sort_order'] = $this->nextSortOrder(
            $project->id,
            $data['milestone_id'] ?? null,
            $data['parent_id'] ?? null
        );

        $task = Task::create($data);
        $this->recalculateProjectWbs($project);
        $task = $task->fresh();
        if (! empty($data['assigned_to'])) {
            $this->sendNotifications($task, 'assigned');
            $this->activityLogger->log(
                'task.assigned',
                __('Task assigned at creation: :title', ['title' => $task->title]),
                $task,
                ['assigned_to' => $task->assigned_to]
            );
        }

        if (($task->status ?? null) === 'done') {
            $this->sendNotifications($task, 'status', null);
        }

        $this->activityLogger->log(
            'task.created',
            __('Task created: :title', ['title' => $task->title]),
            $task,
            ['project_id' => $project->id]
        );

        return $task;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateTask(Task $task, array $data): Task
    {
        return $this->update($task, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task
    {
        $project = $task->project;
        $previousAssignee = $task->assigned_to;
        $previousStatus = $task->status;

        $this->assertParentConsistency($project, $data, $task);
        $task->update($data);
        $this->recalculateProjectWbs($project);
        $task = $task->fresh();

        if (array_key_exists('assigned_to', $data) && (int) ($data['assigned_to'] ?? 0) !== (int) $previousAssignee) {
            $this->sendNotifications($task, 'assigned');
            $this->activityLogger->log(
                'task.assigned',
                __('Assignee changed for task: :title', ['title' => $task->title]),
                $task,
                ['assigned_to' => $task->assigned_to, 'previous_assigned_to' => $previousAssignee]
            );
        }

        if (array_key_exists('status', $data) && $data['status'] !== $previousStatus) {
            $this->sendNotifications($task, 'status', $previousStatus);
        }

        $changed = array_keys(array_intersect_key($data, array_flip([
            'title', 'description', 'status', 'priority', 'deadline', 'milestone_id', 'parent_id',
        ])));

        if ($changed !== []) {
            $this->activityLogger->log(
                'task.updated',
                __('Task updated: :title', ['title' => $task->title]),
                $task,
                ['changed' => $changed]
            );
        }

        return $task;
    }

    public function assignUser(Task $task, User|int|null $assignee): Task
    {
        $id = $assignee instanceof User ? $assignee->getKey() : $assignee;
        if ((int) ($task->assigned_to ?? 0) === (int) ($id ?? 0)) {
            return $task->fresh();
        }

        $task->update(['assigned_to' => $id]);
        $this->recalculateProjectWbs($task->project);
        $task = $task->fresh();
        if ($task->assignee) {
            $this->sendNotifications($task, 'assigned');
        }

        $this->activityLogger->log(
            'task.assigned',
            __('Task assigned: :title', ['title' => $task->title]),
            $task,
            ['assigned_to' => $task->assigned_to]
        );

        return $task;
    }

    public function changeStatus(Task $task, string $status): Task
    {
        $previousStatus = $task->status;
        if ($previousStatus === $status) {
            return $task->fresh();
        }

        $updates = ['status' => $status];
        if ($status === 'done') {
            $updates['completed_at'] = now();
        } else {
            $updates['completed_at'] = null;
        }

        $task->update($updates);
        $this->recalculateProjectWbs($task->project);
        $task = $task->fresh();
        $this->sendNotifications($task, 'status', $previousStatus);

        $this->activityLogger->log(
            'task.updated',
            __('Task status changed: :title → :status', ['title' => $task->title, 'status' => $status]),
            $task,
            ['status' => $status, 'previous_status' => $previousStatus]
        );

        return $task;
    }

    public function markComplete(Task $task): Task
    {
        return $this->changeStatus($task, 'done');
    }

    /**
     * Notify relevant users (assignee, project manager) based on event type.
     */
    public function sendNotifications(Task $task, string $type, ?string $previousStatus = null): void
    {
        $task->loadMissing(['project.manager', 'assignee']);

        $notified = collect();

        if ($type === 'assigned' && $task->assignee) {
            $task->assignee->notify(new TaskAssignedNotification($task));
            $notified->push($task->assignee->getKey());
        }

        if ($type === 'status') {
            $recipients = collect([$task->assignee, $task->project?->manager])->filter()->unique('id');
            foreach ($recipients as $user) {
                if ($notified->contains($user->getKey())) {
                    continue;
                }
                if ($task->status === 'done') {
                    $user->notify(new TaskCompletedNotification($task));
                } else {
                    $user->notify(new TaskStatusChangedNotification($task, $previousStatus));
                }
                $notified->push($user->getKey());
            }
        }
    }

    public function delete(Task $task): void
    {
        $project = $task->project;
        $task->delete();
        $this->recalculateProjectWbs($project);
    }

    public function recalculateProjectWbs(Project $project): void
    {
        $project->loadMissing('milestones');
        foreach ($project->milestones as $milestone) {
            $this->recalculateMilestoneWbs($project->id, $milestone->id);
        }
        $this->recalculateMilestoneWbs($project->id, null);
    }

    private function recalculateMilestoneWbs(int $projectId, ?int $milestoneId): void
    {
        $tasks = Task::query()
            ->where('project_id', $projectId)
            ->when($milestoneId === null, fn ($q) => $q->whereNull('milestone_id'))
            ->when($milestoneId !== null, fn ($q) => $q->where('milestone_id', $milestoneId))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $roots = $tasks->whereNull('parent_id')->values();
        $n = 1;
        foreach ($roots as $root) {
            $this->assignWbsRecursive($root, (string) $n, $tasks);
            $n++;
        }
    }

    private function assignWbsRecursive(Task $task, string $code, $allTasks): void
    {
        DB::table('tasks')->where('id', $task->id)->update(['wbs_code' => $code]);
        $children = $allTasks->where('parent_id', $task->id)->sortBy(['sort_order', 'id'])->values();
        $i = 1;
        foreach ($children as $child) {
            $this->assignWbsRecursive($child, $code.'.'.$i, $allTasks);
            $i++;
        }
    }

    private function nextSortOrder(int $projectId, ?int $milestoneId, ?int $parentId): int
    {
        $q = Task::query()
            ->where('project_id', $projectId)
            ->when($milestoneId === null, fn ($q) => $q->whereNull('milestone_id'))
            ->when($milestoneId !== null, fn ($q) => $q->where('milestone_id', $milestoneId));

        if ($parentId === null) {
            $q->whereNull('parent_id');
        } else {
            $q->where('parent_id', $parentId);
        }

        return (int) $q->max('sort_order') + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertParentConsistency(Project $project, array $data, ?Task $current): void
    {
        $milestoneId = array_key_exists('milestone_id', $data) ? $data['milestone_id'] : $current?->milestone_id;
        $parentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $current?->parent_id;

        if ($parentId === null) {
            return;
        }

        $parent = Task::query()->findOrFail($parentId);
        if ($parent->project_id !== $project->id) {
            abort(422, __('Invalid parent task scope.'));
        }

        $pm = $parent->milestone_id;
        $nm = $milestoneId;
        if ($pm !== $nm) {
            abort(422, __('Parent task must belong to the same milestone (or both backlog).'));
        }

        if ($current) {
            $walk = $parent;
            while ($walk) {
                if ($walk->id === $current->id) {
                    abort(422, __('Cannot set parent to a descendant task.'));
                }
                $walk = $walk->parent_id ? Task::query()->find($walk->parent_id) : null;
            }
        }
    }
}
