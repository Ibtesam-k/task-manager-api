<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskUpdatedNotification;

class NotifyTaskUpdated
{

    /**
     * Handle the event.
     */
    public function handle(TaskUpdated $event): void
    {
        $task = $event->task;
        $reciever = $task->assignee;
        $actor = $event->actor;
        if($reciever && $reciever->id !== $actor->id )
        {
            $reciever->notify(new TaskUpdatedNotification($task,$actor));
        }
    }
}
