<?php

namespace App\Livewire\Task;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Comments extends Component
{
    public int $taskId;

    public string $body = '';

    public function mount(int $taskId): void
    {
        $this->taskId = $taskId;
        $task = Task::query()->findOrFail($taskId);
        abort_unless(auth()->user()->can('comment', $task), 403);
    }

    public function post(): void
    {
        $task = Task::query()->findOrFail($this->taskId);
        abort_unless(auth()->user()->can('comment', $task), 403);
        $this->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        TaskComment::query()->create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);
        $this->body = '';
        $this->dispatch('task-updated', projectId: $task->project_id);
    }

    public function render(): View
    {
        $task = Task::query()->findOrFail($this->taskId);
        abort_unless(auth()->user()->can('comment', $task), 403);
        $comments = TaskComment::query()
            ->where('task_id', $this->taskId)
            ->with('user')
            ->latest()
            ->limit(50)
            ->get();

        return view('livewire.task.comments', [
            'comments' => $comments,
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
