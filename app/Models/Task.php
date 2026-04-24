<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'milestone_id',
        'parent_id',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'deadline',
        'completed_at',
        'sort_order',
        'wbs_code',
        'type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function activitySubjects(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Parents must share the same milestone as the child, or both be backlog (null).
     *
     * @param  Builder<Task>  $query
     */
    public function scopeEligibleParentsFor(Builder $query, int $projectId, ?int $milestoneId): void
    {
        $query->where('project_id', $projectId);

        if ($milestoneId) {
            $query->where('milestone_id', $milestoneId);
        } else {
            $query->whereNull('milestone_id');
        }
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('project-manager')) {
            return $query->whereHas('project', fn ($q) => $q->where('manager_id', $user->id));
        }

        return $query->where('assigned_to', $user->id);
    }
}
