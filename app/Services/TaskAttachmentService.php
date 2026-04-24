<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentService
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function storeMany(Task $task, array $files, ?User $user = null): void
    {
        $disk = 'public';
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $path = $file->store('task-attachments/'.$task->id, $disk);
            TaskAttachment::query()->create([
                'task_id' => $task->id,
                'user_id' => $user?->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize() ?: 0,
                'mime_type' => $file->getClientMimeType(),
            ]);
        }
    }

    public function delete(TaskAttachment $attachment): void
    {
        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        $attachment->delete();
    }
}
