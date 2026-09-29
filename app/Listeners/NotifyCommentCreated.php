<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Notifications\CommentCreatedNotification;

class NotifyCommentCreated
{


    /**
     * Handle the event.
     */
    public function handle(CommentCreated $event): void
    {
        $task = $event->comment->task;
        $actor = $event->comment->creator;

        if (
            $task->assignee !== null &&
            $task->assignee->id !== $actor->id
        ) {
            $task->assignee->notify(
                new CommentCreatedNotification(
                    $task,
                    $actor
                )
            );
        }
    }
}
