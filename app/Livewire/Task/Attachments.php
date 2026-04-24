<?php

namespace App\Livewire\Task;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\TaskAttachmentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Attachments extends Component
{
    use WithFileUploads;

    public int $taskId;

    /** @var array<int, mixed> */
    public array $uploads = [];

    public function mount(int $taskId): void
    {
        $this->taskId = $taskId;
        $task = Task::query()->findOrFail($taskId);
        abort_unless(auth()->user()->can('attach', $task), 403);
    }

    public function saveUploads(TaskAttachmentService $attachments): void
    {
        $task = Task::query()->findOrFail($this->taskId);
        abort_unless(auth()->user()->can('attach', $task), 403);
        $this->validate([
            'uploads.*' => ['file', 'max:10240'],
        ]);

        $attachments->storeMany($task, $this->uploads, auth()->user());
        $this->uploads = [];
        $this->dispatch('task-updated', projectId: $task->project_id);
    }

    public function deleteAttachment(int $attachmentId, TaskAttachmentService $attachments): void
    {
        $attachment = TaskAttachment::query()
            ->where('task_id', $this->taskId)
            ->findOrFail($attachmentId);
        $task = $attachment->task;
        abort_unless(auth()->user()->can('attach', $task), 403);
        $attachments->delete($attachment);
        $this->dispatch('task-updated', projectId: $task->project_id);
    }

    public function render(): View
    {
        $task = Task::query()->findOrFail($this->taskId);
        abort_unless(auth()->user()->can('attach', $task), 403);
        $list = TaskAttachment::query()
            ->where('task_id', $this->taskId)
            ->latest()
            ->get();

        return view('livewire.task.attachments', [
            'attachments' => $list,
        ]);
    }

    private function assertCanAccess(): void
    {
        abort_unless(
            auth()->user()->can('manage-projects') || auth()->user()->can('manage-tasks'),
            403
        );
    }
}
