<?php

namespace App\Livewire\Dashboard;

use App\Models\ProjectReport;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Reports extends Component
{
    public function render(): View
    {
        $reports = ProjectReport::query()
            ->with(['project', 'creator'])
            ->whereHas('project', function ($query) {
                if (auth()->user()->hasRole('admin')) {
                    return;
                }

                if (auth()->user()->hasRole('project-manager')) {
                    // PM sees only reports from their managed projects
                    $query->where('manager_id', auth()->id());
                } else {
                    // TM sees reports from projects where they have assigned tasks
                    $query->whereHas('tasks', function ($tq) {
                        $tq->where('assigned_to', auth()->id());
                    });
                }
            })
            ->latest('generated_at')
            ->limit(10)
            ->get();

        return view('livewire.dashboard.reports', [
            'reports' => $reports,
        ]);
    }
}
