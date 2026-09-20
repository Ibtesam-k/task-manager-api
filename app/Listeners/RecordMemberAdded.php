<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Events\MemberAdded;
use App\Services\ActivityService;

class RecordMemberAdded
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function handle(MemberAdded $event): void
    {
        $this->activityService->record(
            ActivityType::MEMBER_ADDED,
            $event->project,
            $event->user,
            $event->project,
            [
                'added_member_id' => $event->memberId,
            ]
        );
    }
}