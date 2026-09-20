<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Events\MemberRemoved;
use App\Services\ActivityService;

class RecordMemberRemoved
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function handle(MemberRemoved $event): void
    {
        $this->activityService->record(
            ActivityType::MEMBER_REMOVED,
            $event->project,
            $event->user,
            $event->project,
            [
                'removed_member_id' => $event->memberId,
            ]
        );
    }
}
