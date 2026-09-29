<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Notifications\TaskStatusChangedNotification;
use App\Services\ProjectService;


class NotifyTaskStatusChanged
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ProjectService $projectService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(TaskStatusChanged $event): void
    {
        $task = $event->task;
        $actor = $event->user;

        if (
            $task->assignee !== null &&
            $task->assignee->id !== $actor->id
        ) {
            $task->assignee->notify(
                new TaskStatusChangedNotification(
                    $task,
                    $actor,
                    $event->changes,
                )
            );
        }
    }
}
