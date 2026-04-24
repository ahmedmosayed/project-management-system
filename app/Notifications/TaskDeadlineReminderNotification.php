<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeadlineReminderNotification extends Notification
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
        $due = $this->task->deadline?->format('Y-m-d') ?? '—';

        return (new MailMessage)
            ->subject(__('Deadline reminder: :title', ['title' => $this->task->title]))
            ->line(__('Your task is due soon.'))
            ->line($this->task->title)
            ->line(__('Due date: :date', ['date' => $due]))
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
            'kind' => 'task_deadline_reminder',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'deadline' => $this->task->deadline?->format('Y-m-d'),
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project?->name,
            'message' => __('Reminder: :title is due on :date', [
                'title' => $this->task->title,
                'date' => $this->task->deadline?->format('Y-m-d') ?? '—',
            ]),
        ];
    }
}
