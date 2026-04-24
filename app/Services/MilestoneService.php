<?php

namespace App\Services;

use App\Models\Milestone;
use App\Models\Project;

class MilestoneService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Project $project, array $data): Milestone
    {
        $data['project_id'] = $project->id;

        return Milestone::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Milestone $milestone, array $data): Milestone
    {
        $milestone->update($data);

        return $milestone->fresh();
    }

    public function delete(Milestone $milestone): void
    {
        $milestone->delete();
    }
}
