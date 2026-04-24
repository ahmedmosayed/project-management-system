<?php

namespace App\Livewire\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskAttachmentService;
use App\Services\TaskService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?int $project_id = null;

    public ?int $milestone_id = null;

    public ?int $parent_id = null;

    public ?int $assigned_to = null;

    public string $title = '';

    public string $description = '';

    public string $status = 'todo';

    public string $priority = 'medium';

    public ?string $deadline = null;

    public string $successMessage = '';

    /** @var array<int, mixed> */
    public array $attachments = [];

    public function mount(): void
    {
        abort_unless(
            auth()->user()->can('manage-projects') || auth()->user()->can('manage-tasks'),
            403
        );

        $p = request()->query('project');
        $this->project_id = $p !== null && $p !== '' ? (int) $p : null;
    }

    public function updatedProjectId(): void
    {
        $this->milestone_id = null;
        $this->parent_id = null;
    }

    public function updatedMilestoneId(): void
    {
        $this->parent_id = null;
    }

    public function save(TaskService $tasks, TaskAttachmentService $attachmentService): void
    {
        $this->successMessage = '';
        $this->milestone_id = $this->milestone_id ?: null;
        $this->parent_id = $this->parent_id ?: null;

        $rules = [
            'project_id' => ['required', 'exists:projects,id'],
            'milestone_id' => [
                'nullable',
                Rule::exists('milestones', 'id')->where('project_id', $this->project_id),
            ],
            'parent_id' => [
                'nullable',
                Rule::exists('tasks', 'id')->where(function ($query) {
                    $query->where('project_id', $this->project_id);
                    if ($this->milestone_id) {
                        $query->where('milestone_id', $this->milestone_id);
                    } else {
                        $query->whereNull('milestone_id');
                    }
                }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'review', 'done', 'blocked'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'deadline' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ];

        $validated = $this->validate($rules);

        $payload = [
            'milestone_id' => $validated['milestone_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'deadline' => $validated['deadline'],
        ];

        $project = Project::query()->findOrFail($validated['project_id']);
        abort_unless(auth()->user()->can('create', [Task::class, $project]), 403);
        $task = $tasks->createTask($project, $payload);
        $attachmentService->storeMany($task, $this->attachments, auth()->user());

        $attachmentCount = count($this->attachments);
        $this->successMessage = __('Task created successfully!') . ($attachmentCount > 0 ? " $attachmentCount " . __('files attached.') : '');
        $this->dispatch('task-updated', projectId: $task->project_id);

        $this->reset([
            'milestone_id', 'parent_id', 'assigned_to', 'title', 'description',
            'deadline', 'attachments',
        ]);
        $this->status = 'todo';
        $this->priority = 'medium';
    }

    public function render(): View
    {
        $projects = Project::query()
            ->visibleTo(auth()->user())
            ->orderBy('name')
            ->get(['id', 'name']);

        $milestones = $this->project_id
            ? Project::query()
                ->with(['milestones' => fn ($q) => $q->orderBy('id')])
                ->find($this->project_id)?->milestones ?? collect()
            : collect();

        $parentTasks = $this->project_id
            ? Task::query()
                ->eligibleParentsFor((int) $this->project_id, $this->milestone_id ?: null)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'title', 'wbs_code'])
            : collect();

        $teamMembers = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('livewire.task.create', [
            'projects' => $projects,
            'milestones' => $milestones,
            'parentTasks' => $parentTasks,
            'teamMembers' => $teamMembers,
        ]);
    }
}
