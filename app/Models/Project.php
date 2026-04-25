<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'budget',
        'manager_id',
        'status',
        'completed_at',
        'closure_duration_days',
        'closure_performance_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
            'status' => ProjectStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProjectReport::class)->latest();
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            if ($user->hasRole('project-manager')) {
                $q->orWhere('manager_id', $user->id);
            }

            if ($user->hasRole('team-member')) {
                $q->orWhereHas('tasks', function ($sq) use ($user) {
                    $sq->where('assigned_to', $user->id);
                });
            }
        });
    }

    public function progressPercent(?User $user = null): float
    {
        $query = $this->tasks();
        if ($user && $user->hasRole('team-member')) {
            $query->where('assigned_to', $user->id);
        }

        $total = $query->count();
        if ($total === 0) {
            return 0.0;
        }

        $done = (clone $query)->where('status', 'done')->count();

        return round(($done / $total) * 100, 1);
    }
}
