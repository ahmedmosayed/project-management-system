<?php

namespace App\Livewire\Project;

use App\Enums\ProjectStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\Task;
use App\Models\User;
use App\Services\MilestoneService;
use App\Services\ProjectService;
use App\Services\TaskAttachmentService;
use App\Services\TaskService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Project $project;

    public ?ProjectReport $latestReport = null;

    private $tasks;

    public bool $milestoneModalOpen = false;

    public ?int $editingMilestoneId = null;

    public string $ms_title = '';

    public ?string $ms_due_date = null;

    public string $ms_status = 'pending';

    public bool $taskModalOpen = false;

    public ?int $editingTaskId = null;

    public ?int $tk_milestone_id = null;

    public ?int $tk_parent_id = null;

    public ?int $tk_assigned_to = null;

    public string $tk_title = '';

    public string $tk_description = '';

    public string $tk_status = 'todo';

    public string $tk_priority = 'medium';

    public ?string $tk_deadline = null;

    /** @var array<int, mixed> */
    public array $tk_uploads = [];

    public ?string $bannerMessage = null;

    public function mount(Project $project): void
    {
        abort_unless(auth()->user()->can('view', $project), 403);
        $this->project = $project->load('manager');
        $this->latestReport = $project->reports()->first();
        $this->tasks = collect();
        $this->loadTasks();
    }

    public function refreshProject(): void
    {
        $this->project = Project::query()
            ->with(['manager', 'milestones' => fn ($q) => $q->orderBy('id')])
            ->findOrFail($this->project->id);
        $this->latestReport = $this->project->reports()->first();
        $this->tasks = collect();
        $this->loadTasks();
    }

    public function openMilestoneModal(?int $milestoneId = null): void
    {
        abort_unless(auth()->user()->can('update', $this->project), 403);
        $this->editingMilestoneId = $milestoneId;
        if ($milestoneId) {
            $m = Milestone::query()->where('project_id', $this->project->id)->findOrFail($milestoneId);
            $this->ms_title = $m->title;
            $this->ms_due_date = $m->due_date?->format('Y-m-d');
            $this->ms_status = $m->status;
        } else {
            $this->reset(['ms_title', 'ms_due_date']);
            $this->ms_status = 'pending';
        }
        $this->milestoneModalOpen = true;
    }

    public function closeMilestoneModal(): void
    {
        $this->milestoneModalOpen = false;
        $this->editingMilestoneId = null;
    }

    public function saveMilestone(MilestoneService $milestones): void
    {
        abort_unless(auth()->user()->can('update', $this->project), 403);
        $validated = $this->validate([
            'ms_title' => ['required', 'string', 'max:255'],
            'ms_due_date' => ['nullable', 'date'],
            'ms_status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
        ]);

        $payload = [
            'title' => $validated['ms_title'],
            'due_date' => $validated['ms_due_date'],
            'status' => $validated['ms_status'],
        ];

        if ($this->editingMilestoneId) {
            $m = Milestone::query()
                ->where('project_id', $this->project->id)
                ->findOrFail($this->editingMilestoneId);
            $milestones->update($m, $payload);
            $this->bannerMessage = __('Milestone updated.');
        } else {
            $milestones->create($this->project, $payload);
            $this->bannerMessage = __('Milestone created.');
        }

        $this->closeMilestoneModal();
        $this->refreshProject();
    }

    public function deleteMilestone(int $milestoneId, MilestoneService $milestones): void
    {
        abort_unless(auth()->user()->can('update', $this->project), 403);
        $m = Milestone::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($milestoneId);
        $milestones->delete($m);
        $this->bannerMessage = __('Milestone deleted.');
        $this->refreshProject();
    }

    public function openTaskModal(?int $taskId = null, ?int $milestoneId = null, ?int $parentId = null): void
    {
        $this->editingTaskId = $taskId;
        $this->tk_milestone_id = $milestoneId;
        $this->tk_parent_id = $parentId;
        $this->tk_uploads = [];

        $this->project_id = $this->project->id;

        if ($taskId) {
            $t = Task::query()
                ->where('project_id', $this->project->id)
                ->findOrFail($taskId);
            abort_unless(auth()->user()->can('update', $t), 403);
            $this->tk_milestone_id = $t->milestone_id;
            $this->tk_parent_id = $t->parent_id;
            $this->tk_assigned_to = $t->assigned_to;
            $this->tk_title = $t->title;
            $this->tk_description = (string) $t->description;
            $this->tk_status = $t->status;
            $this->tk_priority = $t->priority;
            $this->tk_deadline = $t->deadline?->format('Y-m-d');
        } else {
            abort_unless(auth()->user()->can('create', [Task::class, $this->project]), 403);
            $this->reset(['tk_assigned_to', 'tk_title', 'tk_description', 'tk_deadline']);
            $this->tk_status = 'todo';
            $this->tk_priority = 'medium';
        }

        $this->taskModalOpen = true;
    }

    public function closeTaskModal(): void
    {
        $this->taskModalOpen = false;
        $this->editingTaskId = null;
        $this->tk_parent_id = null;
        $this->tk_uploads = [];
    }

    public function markProjectCompleted(ProjectService $projects): void
    {
        abort_unless(auth()->user()->can('close', $this->project), 403);
        if ($this->project->status === ProjectStatus::Completed) {
            $this->bannerMessage = __('Project is already completed.');

            return;
        }
        $projects->update($this->project, ['status' => ProjectStatus::Completed]);
        $this->bannerMessage = __('Project marked as completed. Notifications and closure summary were recorded.');
        $this->refreshProject();
        $this->dispatch('project-updated');
    }

    public function saveTask(TaskService $tasks, TaskAttachmentService $attachments): void
    {
        $this->tk_milestone_id = $this->tk_milestone_id ?: null;
        $this->tk_parent_id = $this->tk_parent_id ?: null;
        $this->tk_assigned_to = $this->tk_assigned_to ?: null;

        $rules = [
            'tk_milestone_id' => [
                'nullable',
                Rule::exists('milestones', 'id')->where('project_id', $this->project->id),
            ],
            'tk_parent_id' => [
                'nullable',
                Rule::exists('tasks', 'id')->where('project_id', $this->project->id),
            ],
            'tk_assigned_to' => ['nullable', 'exists:users,id'],
            'tk_title' => ['required', 'string', 'max:255'],
            'tk_description' => ['nullable', 'string'],
            'tk_status' => ['required', Rule::in(['todo', 'in_progress', 'review', 'done', 'blocked'])],
            'tk_priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'tk_deadline' => ['nullable', 'date'],
            'tk_uploads' => ['nullable', 'array'],
            'tk_uploads.*' => ['file', 'max:10240'],
        ];

        $validated = $this->validate($rules);

        $payload = [
            'milestone_id' => $validated['tk_milestone_id'] ?? null,
            'parent_id' => $validated['tk_parent_id'] ?? null,
            'assigned_to' => $validated['tk_assigned_to'] ?? null,
            'title' => $validated['tk_title'],
            'description' => $validated['tk_description'] ?: null,
            'status' => $validated['tk_status'],
            'priority' => $validated['tk_priority'],
            'deadline' => $validated['tk_deadline'],
        ];

        if ($this->editingTaskId) {
            $task = Task::query()
                ->where('project_id', $this->project->id)
                ->findOrFail($this->editingTaskId);
            abort_unless(auth()->user()->can('update', $task), 403);
            $tasks->update($task, $payload);
            $attachments->storeMany($task, $this->tk_uploads, auth()->user());
            $this->bannerMessage = __('Task updated.');
        } else {
            abort_unless(auth()->user()->can('create', [Task::class, $this->project]), 403);
            $task = $tasks->create($this->project, $payload);
            $attachments->storeMany($task, $this->tk_uploads, auth()->user());
            $attachmentCount = count($this->tk_uploads);
            $this->bannerMessage = __('Task created successfully!') . ($attachmentCount > 0 ? " $attachmentCount " . __('files attached.') : '');
        }

        $this->tk_uploads = [];
        $this->closeTaskModal();
        $this->refreshProject();
        $this->dispatch('task-updated', projectId: $this->project->id);
    }

    public function deleteTask(int $taskId, TaskService $tasks): void
    {
        $task = Task::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($taskId);
        abort_unless(auth()->user()->can('delete', $task), 403);
        $tasks->delete($task);
        $this->bannerMessage = __('Task deleted.');
        $this->refreshProject();
    }

    public function createReportTask(TaskService $tasks): void
    {
        abort_unless(auth()->user()->can('update', $this->project), 403);
        // Create the report task
        $payload = [
            'title' => 'Generate Project Report',
            'description' => 'Generate a detailed report for project: ' . $this->project->name,
            'status' => 'todo',
            'priority' => 'medium',
            'type' => 'report',
        ];

        $task = $tasks->create($this->project, $payload);

        // Generate the report immediately
        $reports = app(\App\Services\ProjectReportService::class);
        $reports->generateReport($this->project, $task, auth()->user());

        // Mark the task as completed
        $tasks->changeStatus($task, 'done');

        $this->bannerMessage = __('Report generated successfully.');
        $this->refreshProject();
    }

    public function dismissBanner(): void
    {
        $this->bannerMessage = null;
    }

    private function loadTasks(): void
    {
        $this->tasks = Task::query()
            ->where('project_id', $this->project->id)
            ->with('assignee')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    #[On('task-updated')]
    public function onTaskUpdated(?int $projectId = null): void
    {
        if ($projectId !== null && (int) $projectId !== (int) $this->project->id) {
            return;
        }

        $this->refreshProject();
    }

    /**
     * @return Collection<int, Task>
     */
    private function tasksForMilestone(?int $milestoneId): Collection
    {
        if (! $this->tasks) {
            return collect();
        }
        return $this->tasks->filter(fn ($task) => $task->milestone_id == $milestoneId)->sortBy('sort_order')->sortBy('id');
    }

    /**
     * @return Collection<int, Task>
     */
    private function buildTaskForest(Collection $flat): Collection
    {
        foreach ($flat as $task) {
            $task->setRelation('treeChildren', collect());
        }
        foreach ($flat as $task) {
            if ($task->parent_id) {
                $parent = $flat->firstWhere('id', $task->parent_id);
                if ($parent) {
                    $parent->treeChildren->push($task);
                }
            }
        }
        foreach ($flat as $task) {
            $task->setRelation(
                'treeChildren',
                $task->treeChildren->sortBy(['sort_order', 'id'])->values()
            );
        }

        return $flat->whereNull('parent_id')->sortBy(['sort_order', 'id'])->values();
    }

    public function render(): View
    {
        $this->project->load(['milestones' => fn ($q) => $q->orderBy('id')]);

        $milestoneTrees = [];
        foreach ($this->project->milestones as $milestone) {
            $milestoneTrees[$milestone->id] = $this->buildTaskForest($this->tasksForMilestone($milestone->id));
        }

        $backlogForest = $this->buildTaskForest($this->tasksForMilestone(null));

        $teamMembers = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('livewire.project.show', [
            'milestoneTrees' => $milestoneTrees,
            'backlogForest' => $backlogForest,
            'teamMembers' => $teamMembers,
        ]);
    }
}
