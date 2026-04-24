<?php

namespace App\Livewire\Project;

use App\Enums\ProjectStatus;
use App\Services\ProjectService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $description = '';

    public ?string $start_date = null;

    public ?string $end_date = null;

    public ?string $budget = null;

    public ?int $manager_id = null;

    public string $status = '';

    public string $successMessage = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $this->status = ProjectStatus::Planning->value;
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'manager_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
        ];
    }

    public function save(ProjectService $projects): void
    {
        $this->successMessage = '';
        $this->budget = $this->budget === '' ? null : $this->budget;
        $validated = $this->validate();
        $validated['budget'] = $this->normalizeBudget($validated['budget'] ?? null);

        $projects->create($validated);

        $this->successMessage = __('Project created successfully.');
        $this->reset(['name', 'description', 'start_date', 'end_date', 'budget', 'manager_id']);
        $this->status = ProjectStatus::Planning->value;
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
        $managers = \App\Models\User::query()
            ->role(['admin', 'project-manager'])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        if ($managers->isEmpty()) {
            $managers = \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'email']);
        }

        return view('livewire.project.create', [
            'managers' => $managers,
            'statuses' => ProjectStatus::cases(),
        ]);
    }
}
