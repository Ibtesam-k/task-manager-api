<?php

namespace App\Listeners;

use App\Events\TaskAssignmentChanged;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskUnassignedNotification;

class NotifyTaskAssignmentChanged
{

    /**
     * Handle the event.
     */
    public function handle(TaskAssignmentChanged $event): void
    {
        $task = $event->task;
        $actor = $event->user;

        if (
            $event->newAssigneeId !== null &&
            $event->newAssigneeId !== $actor->id
        ) {
            $task->assignee?->notify(
                new TaskAssignedNotification($task, $actor)
            );
        }

        if (
            $event->oldAssigneeId !== null &&
            $event->oldAssigneeId !== $actor->id
        ) {
            User::find($event->oldAssigneeId)?->notify(
                new TaskUnassignedNotification($task, $actor)
            );
        }
    }
}
