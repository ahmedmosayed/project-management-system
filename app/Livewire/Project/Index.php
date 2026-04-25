<?php

namespace App\Livewire\Project;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $managerFilter = '';

    public bool $showEditModal = false;

    public ?int $editingProjectId = null;

    public string $edit_name = '';

    public string $edit_description = '';

    public ?string $edit_start_date = null;

    public ?string $edit_end_date = null;

    public ?string $edit_budget = null;

    public ?int $edit_manager_id = null;

    public string $edit_status = '';

    public ?string $bannerMessage = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedManagerFilter(): void
    {
        $this->resetPage();
    }

    public function openEdit(int $projectId): void
    {
        $project = Project::query()->with('manager')->findOrFail($projectId);
        abort_unless(auth()->user()->can('update', $project), 403);

        $this->editingProjectId = $project->id;
        $this->edit_name = $project->name;
        $this->edit_description = (string) $project->description;
        $this->edit_start_date = $project->start_date?->format('Y-m-d');
        $this->edit_end_date = $project->end_date?->format('Y-m-d');
        $this->edit_budget = $project->budget !== null ? (string) $project->budget : null;
        $this->edit_manager_id = $project->manager_id;
        $this->edit_status = $project->status->value;
        $this->showEditModal = true;
    }

    public function closeEdit(): void
    {
        $this->showEditModal = false;
        $this->editingProjectId = null;
    }

    public function updateProject(ProjectService $projects): void
    {
        $project = Project::query()->findOrFail($this->editingProjectId);
        abort_unless(auth()->user()->can('update', $project), 403);
        $this->edit_budget = $this->edit_budget === '' ? null : $this->edit_budget;

        $validated = $this->validate($this->editRules());
        $payload = [
            'name' => $validated['edit_name'],
            'description' => $validated['edit_description'],
            'start_date' => $validated['edit_start_date'],
            'end_date' => $validated['edit_end_date'],
            'budget' => $this->normalizeBudget($validated['edit_budget'] ?? null),
            'manager_id' => $validated['edit_manager_id'],
            'status' => $validated['edit_status'],
        ];

        $projects->update($project, $payload);
        $this->bannerMessage = __('Project updated successfully.');
        $this->dispatch('project-updated');
        $this->closeEdit();
    }

    public function deleteProject(int $projectId, ProjectService $projectService): void
    {
        $project = Project::query()->findOrFail($projectId);
        abort_unless(auth()->user()->can('delete', $project), 403);
        $projectService->delete($project);
        $this->bannerMessage = __('Project deleted successfully.');
    }

    public function closeProject(int $projectId, ProjectService $projectService): void
    {
        $project = Project::query()->findOrFail($projectId);
        abort_unless(auth()->user()->can('close', $project), 403);
        $projectService->closeProject($project);
        $this->bannerMessage = __('Project closed successfully.');
    }

    public function dismissBanner(): void
    {
        $this->bannerMessage = null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function editRules(): array
    {
        return [
            'edit_name' => ['required', 'string', 'max:255'],
            'edit_description' => ['nullable', 'string'],
            'edit_start_date' => ['nullable', 'date'],
            'edit_end_date' => ['nullable', 'date', 'after_or_equal:edit_start_date'],
            'edit_budget' => ['nullable', 'numeric', 'min:0'],
            'edit_manager_id' => ['required', 'integer', 'exists:users,id'],
            'edit_status' => ['required', Rule::enum(ProjectStatus::class)],
        ];
    }

    private function normalizeBudget(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_string($value) ? $value : (string) $value;
    }

    public function render()
    {
        $projects = Project::query()
            ->visibleTo(auth()->user())
            ->with('manager')
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->managerFilter !== '', function ($q) {
                $q->where('manager_id', (int) $this->managerFilter);
            })
            ->latest()
            ->paginate(10);

        // Only admins can filter by manager - PMs only see their own projects
        $managerOptions = auth()->user()->hasRole('admin')
            ? \App\Models\User::query()
                ->role('project-manager')
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
            : collect();

        return view('livewire.project.index', [
            'projects' => $projects,
            'managerOptions' => $managerOptions,
            'statuses' => ProjectStatus::cases(),
        ]);
    }
}
