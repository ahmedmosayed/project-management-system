<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public Project $project) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Project completed: :name', ['name' => $this->project->name]))
            ->line(__('The project has been marked as completed.'))
            ->line($this->project->name)
            ->action(
                $notifiable->can('view', $this->project) ? __('Open project') : __('View tasks'),
                $notifiable->can('view', $this->project) ? route('projects.show', $this->project->id) : route('tasks.index')
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'project_completed',
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'message' => __('Project :name is completed', ['name' => $this->project->name]),
        ];
    }
}
