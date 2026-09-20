<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Events\CommentCreated;
use App\Services\ActivityService;


class RecordCommentCreated
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
    public function handle(CommentCreated $event): void
    {
        $this->activityService->record(
            ActivityType::COMMENT_CREATED,
            $event->comment->task->project,
            $event->comment->creator,
            $event->comment
        );
    }
}
