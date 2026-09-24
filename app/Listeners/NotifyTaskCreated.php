<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Notifications\TaskCreatedNotification;
use App\Services\ProjectService;


class NotifyTaskCreated
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
        public function handle(TaskCreated $event): void
        {
            $task = $event->task;
            $actor = $task->creator;
            $project = $task->project;

            $receivers = $this->projectService
                ->getProjectOwners($project)
                ->where('id', '!=', $actor->id);

            $notification = new TaskCreatedNotification($task, $actor);

            foreach ($receivers as $receiver) {
                $receiver->notify($notification);
            }
        }
}
