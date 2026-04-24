<?php

namespace App\Livewire\Task;

use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Board extends Component
{
    /** @var list<string> */
    public const STATUSES_PENDING = ['todo', 'blocked'];

    /** @var list<string> */
    public const STATUSES_IN_PROGRESS = ['in_progress', 'review'];

    /** @var list<string> */
    public const STATUSES_COMPLETED = ['done'];

    public string $projectFilter = '';

    public function mount(): void
    {
        abort_unless(
            auth()->user()->can('view-tasks') || auth()->user()->can('manage-projects') || auth()->user()->can('manage-tasks'),
            403
        );
    }

    public function render(): View
    {
        $base = Task::query()
            ->visibleTo(auth()->user())
            ->with(['project', 'assignee'])
            ->when($this->projectFilter !== '', fn ($q) => $q->where('project_id', (int) $this->projectFilter))
            ->orderBy('project_id')
            ->orderBy('sort_order')
            ->orderBy('id');

        $all = $base->get();

        $pending = $all->filter(fn (Task $t) => in_array($t->status, self::STATUSES_PENDING, true))->values();
        $inProgress = $all->filter(fn (Task $t) => in_array($t->status, self::STATUSES_IN_PROGRESS, true))->values();
        $completed = $all->filter(fn (Task $t) => in_array($t->status, self::STATUSES_COMPLETED, true))->values();

        $projects = Project::query()->visibleTo(auth()->user())->orderBy('name')->get(['id', 'name']);

        return view('livewire.task.board', [
            'pendingTasks' => $pending,
            'inProgressTasks' => $inProgress,
            'completedTasks' => $completed,
            'projects' => $projects,
        ]);
    }

    public function moveToColumn(int $taskId, string $toCol): void
    {
        $task = Task::query()->visibleTo(auth()->user())->findOrFail($taskId);
        $this->assertCanManageTask($task);

        $newStatus = match ($toCol) {
            'pending' => 'todo',
            'in_progress' => 'in_progress',
            'completed' => 'done',
            default => null,
        };

        if ($newStatus && $task->status !== $newStatus) {
            $tasks = app(TaskService::class);
            $tasks->changeStatus($task, $newStatus);
        }
    }

    /**
     * @param array<int, int> $ids
     */
    public function syncColumnOrder(string $col, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $tasks = Task::query()
            ->visibleTo(auth()->user())
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        foreach ($ids as $index => $id) {
            if ($task = $tasks->get($id)) {
                $this->assertCanManageTask($task);
                if ($task->sort_order !== $index) {
                    $task->forceFill(['sort_order' => $index])->save();
                }
            }
        }
    }

    private function assertCanManageTask(Task $task): void
    {
        abort_unless(auth()->user()->can('changeStatus', $task), 403);
    }
}
