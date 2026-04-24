<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDeadlineReminderNotification;
use Illuminate\Console\Command;

class SendTaskDeadlineReminders extends Command
{
    protected $signature = 'tasks:send-deadline-reminders';

    protected $description = 'Email assignees for tasks due tomorrow (not completed)';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $tasks = Task::query()
            ->whereNotNull('deadline')
            ->whereNotNull('assigned_to')
            ->where('status', '!=', 'done')
            ->whereDate('deadline', $tomorrow)
            ->with(['assignee', 'project'])
            ->get();

        $sent = 0;
        foreach ($tasks as $task) {
            if ($task->assignee) {
                $task->assignee->notify(new TaskDeadlineReminderNotification($task));
                $sent++;
            }
        }

        $this->info("Sent {$sent} deadline reminder(s) for tasks due on {$tomorrow}.");

        return self::SUCCESS;
    }
}
