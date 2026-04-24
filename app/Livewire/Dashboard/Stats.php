<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Stats extends Component
{
    public int $refreshTrigger = 0;

    #[On('task-updated')]
    public function onTaskUpdated(): void
    {
        $this->refreshTrigger++;
    }

    public function render(DashboardService $dashboard): View
    {
        $stats = $dashboard->getStats(auth()->user());

        return view('livewire.dashboard.stats', [
            'stats' => $stats,
        ]);
    }
}
