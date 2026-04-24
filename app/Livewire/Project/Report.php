<?php

namespace App\Livewire\Project;

use App\Models\ProjectReport;
use Livewire\Component;

class Report extends Component
{
    public ProjectReport $report;

    public function render()
    {
        return view('livewire.project.report');
    }
}
