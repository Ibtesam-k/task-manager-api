<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Events\TaskCreated;
use App\Services\ActivityService;


class RecordTaskCreated
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function handle(TaskCreated $event): void
    {
        $this->activityService->record(
            ActivityType::TASK_CREATED,
            $event->task->project,
            $event->task->creator,
            $event->task
        );
    }
}
