<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public ?string $previousStatus = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->task->loadMissing('project');

        return (new MailMessage)
            ->subject(__('Task status: :status — :title', ['status' => $this->task->status, 'title' => $this->task->title]))
            ->line(__('Status changed to :status.', ['status' => $this->task->status]))
            ->line($this->task->title)
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
            'kind' => 'task_status_changed',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'status' => $this->task->status,
            'previous_status' => $this->previousStatus,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project?->name,
            'message' => __('Task :title is now :status', ['title' => $this->task->title, 'status' => $this->task->status]),
        ];
    }
}
