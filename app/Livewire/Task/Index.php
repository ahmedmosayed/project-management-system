<?php

namespace App\Livewire\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $statusFilter = '';

    public bool $editModalOpen = false;

    public ?int $editingTaskId = null;

    public ?int $ed_project_id = null;

    public ?int $ed_milestone_id = null;

    public ?int $ed_parent_id = null;

    public string $ed_title = '';

    public string $ed_description = '';

    public string $ed_status = 'todo';

    public string $ed_priority = 'medium';

    public ?string $ed_deadline = null;

    public function mount(): void
    {
        abort_unless(
            auth()->user()->can('view-tasks') || auth()->user()->can('manage-projects') || auth()->user()->can('manage-tasks'),
            403
        );
    }

    public function updateInlineStatus(int $taskId, string $status): void
    {
        $task = Task::query()->with('project')->findOrFail($taskId);
        abort_unless(auth()->user()->can('changeStatus', $task), 403);

        if ($task->status === $status) {
            return;
        }

        app(TaskService::class)->changeStatus($task, $status);
        $this->dispatch('task-updated', projectId: $task->project_id);
    }

    public bool $canUpdateTask = false;

    public function openEditModal(int $taskId): void
    {
        $task = Task::query()->with('project')->findOrFail($taskId);
        abort_unless(auth()->user()->can('view', $task), 403);
        $this->canUpdateTask = auth()->user()->can('update', $task);

        $this->editingTaskId = $task->id;
        $this->ed_project_id = $task->project_id;
        $this->ed_milestone_id = $task->milestone_id;
        $this->ed_parent_id = $task->parent_id;
        $this->ed_title = $task->title;
        $this->ed_description = (string) $task->description;
        $this->ed_status = $task->status;
        $this->ed_priority = $task->priority;
        $this->ed_deadline = $task->deadline?->format('Y-m-d');

        $this->editModalOpen = true;
    }

    public function updatedEdMilestoneId(): void
    {
        $this->ed_parent_id = null;
    }

    public function closeEditModal(): void
    {
        $this->editModalOpen = false;
        $this->editingTaskId = null;
    }

    public function saveEdit(TaskService $tasks): void
    {
        $task = Task::query()->findOrFail($this->editingTaskId);
        abort_unless(auth()->user()->can('update', $task), 403);

        $this->ed_milestone_id = $this->ed_milestone_id ?: null;
        $this->ed_parent_id = $this->ed_parent_id ?: null;

        $rules = [
            'ed_project_id' => ['required', 'exists:projects,id'],
            'ed_milestone_id' => [
                'nullable',
                Rule::exists('milestones', 'id')->where('project_id', $this->ed_project_id),
            ],
            'ed_parent_id' => [
                'nullable',
                Rule::exists('tasks', 'id')->where(function ($query) {
                    $query->where('project_id', $this->ed_project_id);
                    if ($this->ed_milestone_id) {
                        $query->where('milestone_id', $this->ed_milestone_id);
                    } else {
                        $query->whereNull('milestone_id');
                    }
                }),
            ],
            'ed_title' => ['required', 'string', 'max:255'],
            'ed_description' => ['nullable', 'string'],
            'ed_status' => ['required', Rule::in(['todo', 'in_progress', 'review', 'done', 'blocked'])],
            'ed_priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'ed_deadline' => ['nullable', 'date'],
        ];

        $validated = $this->validate($rules);

        $payload = [
            'milestone_id' => $validated['ed_milestone_id'] ?? null,
            'parent_id' => $validated['ed_parent_id'] ?? null,
            'title' => $validated['ed_title'],
            'description' => $validated['ed_description'] ?: null,
            'status' => $validated['ed_status'],
            'priority' => $validated['ed_priority'],
            'deadline' => $validated['ed_deadline'],
        ];

        $project = Project::query()->findOrFail($validated['ed_project_id']);
        if ((int) $task->project_id !== (int) $project->id) {
            abort(422, __('Cannot move task to another project from this form.'));
        }

        $tasks->updateTask($task, $payload);
        $this->dispatch('task-updated', projectId: $task->project_id);
        $this->closeEditModal();
    }

    public function deleteTask(int $taskId, TaskService $tasks): void
    {
        $task = Task::query()->findOrFail($taskId);
        abort_unless(auth()->user()->can('delete', $task), 403);
        $projectId = $task->project_id;
        $tasks->delete($task);
        $this->dispatch('task-updated', projectId: $projectId);
    }

    public function render(): View
    {
        $query = Task::query()
            ->visibleTo(auth()->user())
            ->with(['project', 'milestone', 'assignee'])
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('updated_at');

        $tasks = $query->paginate(15);

        $usersForFilter = User::query()->role('team-member')->orderBy('name')->get(['id', 'name']);

        $projectsForEdit = Project::query()->visibleTo(auth()->user())->orderBy('name')->get(['id', 'name']);

        $milestonesForEdit = $this->ed_project_id
            ? Project::query()
                ->with(['milestones' => fn ($q) => $q->orderBy('id')])
                ->find($this->ed_project_id)?->milestones ?? collect()
            : collect();

        $parentTasksForEdit = $this->ed_project_id
            ? Task::query()
                ->eligibleParentsFor((int) $this->ed_project_id, $this->ed_milestone_id ?: null)
                ->when($this->editingTaskId, fn ($q) => $q->where('id', '!=', $this->editingTaskId))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'title', 'wbs_code'])
            : collect();

        return view('livewire.task.index', [
            'tasks' => $tasks,
            'usersForFilter' => $usersForFilter,
            'projectsForEdit' => $projectsForEdit,
            'milestonesForEdit' => $milestonesForEdit,
            'parentTasksForEdit' => $parentTasksForEdit,
        ]);
    }

    private function assertCanManageTask(Task $task): void
    {
        abort_unless(
            auth()->user()->can('manage-projects') || auth()->user()->can('manage-tasks'),
            403
        );
    }
}
