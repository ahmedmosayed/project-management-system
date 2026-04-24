<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $properties
     */
    public function log(string $event, string $description, ?Model $subject = null, ?array $properties = null): void
    {
        $user = Auth::user();

        Activity::query()->create([
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'causer_type' => $user instanceof User ? $user->getMorphClass() : null,
            'causer_id' => $user?->getKey(),
            'properties' => $properties,
        ]);
    }
}
