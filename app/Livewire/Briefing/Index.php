<?php

namespace App\Livewire\Briefing;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public int $refreshTrigger = 0;

    #[On('task-updated')]
    public function onTaskUpdated(): void
    {
        $this->refreshTrigger++;
    }

    public function render(DashboardService $dashboard): View
    {
        // Check if user has permission to view briefing
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('project-manager')) {
            abort(403, 'Unauthorized');
        }

        $briefing = $dashboard->getBriefing(auth()->user());

        return view('livewire.briefing.index', [
            'briefing' => $briefing,
        ]);
    }
}
