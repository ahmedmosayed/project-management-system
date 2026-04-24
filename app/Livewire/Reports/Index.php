<?php

namespace App\Livewire\Reports;

use App\Models\ProjectReport;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public function render(): View
    {
        $reports = ProjectReport::query()
            ->with(['project', 'creator'])
            ->when(! auth()->user()->hasRole('admin'), function ($query) {
                if (auth()->user()->hasRole('project-manager')) {
                    // PM sees only reports from their managed projects
                    $query->whereHas('project', function ($q) {
                        $q->where('manager_id', auth()->id());
                    });
                } else {
                    // TM sees reports from projects where they have assigned tasks
                    $query->whereHas('project', function ($q) {
                        $q->whereHas('tasks', function ($tq) {
                            $tq->where('assigned_to', auth()->id());
                        });
                    });
                }
            })
            ->when($this->search, function ($query) {
                $query->whereHas('project', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->latest('generated_at')
            ->paginate(20);

        return view('livewire.reports.index', [
            'reports' => $reports,
        ]);
    }
}
