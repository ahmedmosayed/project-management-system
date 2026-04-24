<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReport extends Model
{
    protected $fillable = [
        'project_id',
        'task_id',
        'created_by',
        'generated_at',
        'completed_at',
        'summary_data',
        'detailed_data',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'completed_at' => 'datetime',
            'summary_data' => 'array',
            'detailed_data' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
