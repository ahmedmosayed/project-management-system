<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;

class TaskCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->task->loadMissing('project');

        return (new MailMessage)
            ->subject(__('Task completed: :title', ['title' => $this->task->title]))
            ->line(__('This task is marked as done.'))
            ->line($this->task->title)
            ->line(__('Project: :name', ['name' => $this->task->project?->name ?? '—']))
            ->action(
                $notifiable->can('view', $this->task->project) ? __('Open project') : __('View tasks'),
                $notifiable->can('view', $this->task->project) ? route('projects.show', $this->task->project_id) : route('tasks.index')
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->task->loadMissing('project');

        return [
            'kind' => 'task_completed',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project?->name,
            'message' => __('Task completed: :title', ['title' => $this->task->title]),
        ];
    }
}
