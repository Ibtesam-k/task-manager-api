<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Events\TaskStatusChanged;
use App\Services\ActivityService; 


class RecordTaskStatusChanged
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ActivityService $activityService
    ) {}


    /**
     * Handle the event.
     */
    public function handle(TaskStatusChanged $event): void
    {
        $this->activityService->record(
            ActivityType::TASK_STATUS_CHANGED,
            $event->task->project,
            $event->user,
            $event->task,
            $event->changes

            );
    }
}
