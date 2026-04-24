<?php

namespace App\Livewire\Activity;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Feed extends Component
{
    public int $projectId;

    #[On('task-updated')]
    #[On('project-updated')]
    public function refreshOnProjectEvents(?int $projectId = null): void
    {
        if ($projectId !== null && $projectId !== $this->projectId) {
            return;
        }
    }

    public function mount(int $projectId): void
    {
        abort_unless(
            auth()->user()->can('manage-projects') || auth()->user()->can('manage-tasks'),
            403
        );
        $this->projectId = $projectId;
    }

    public function render(): View
    {
        $taskIds = Task::query()->where('project_id', $this->projectId)->pluck('id');

        $items = Activity::query()
            ->where(function ($q) use ($taskIds) {
                $q->where(function ($q2) {
                    $q2->where('subject_type', Project::class)
                        ->where('subject_id', $this->projectId);
                });
                $q->orWhere(function ($q2) use ($taskIds) {
                    $q2->where('subject_type', Task::class)
                        ->whereIn('subject_id', $taskIds);
                });
            })
            ->with('causer')
            ->latest()
            ->limit(40)
            ->get();

        return view('livewire.activity.feed', [
            'activities' => $items,
        ]);
    }
}
