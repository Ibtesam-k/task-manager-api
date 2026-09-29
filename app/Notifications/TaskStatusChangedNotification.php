<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $actor,
        public array $changes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => sprintf(
                'Task "%s" status changed from "%s" to "%s" by %s.',
                $this->task->title,
                $this->changes['old_status'],
                $this->changes['new_status'],
                $this->actor->name,
            ),
            'task_uuid' => $this->task->uuid,
            'type' => NotificationType::TASK_STATUS_CHANGED->value,
        ];
    }
}

