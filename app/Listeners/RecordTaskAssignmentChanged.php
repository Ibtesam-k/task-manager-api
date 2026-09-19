<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Events\TaskAssignmentChanged;
use App\Services\ActivityService;


class RecordTaskAssignmentChanged
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function handle(TaskAssignmentChanged $event): void
    {
        $type = $event->newAssigneeId === null
            ? ActivityType::TASK_UNASSIGNED
            : ActivityType::TASK_ASSIGNED;

        $this->activityService->record(
            $type,
            $event->task->project,
            $event->user,
            $event->task,
            [
                'old_assignee_id' => $event->oldAssigneeId,
                'new_assignee_id' => $event->newAssigneeId,
            ]
        );
    }
}



