<?php

namespace App\Listeners;

use App\Events\MemberRemoved;
use App\Models\User;
use App\Notifications\MemberRemovedNotification;
use App\Notifications\RemovedMemberNotification;
use App\Services\ProjectService;


class NotifyMemberRemoved
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
    public function handle(MemberRemoved $event): void
    {
        $actor = $event->user;
        $project = $event->project;

        $member = User::find($event->memberId);

        if (!$member) {
            return;
        }
        $receivers = $this->projectService
                ->getProjectOwners($project)
                ->where('id', '!=', $actor->id);

        $ownersNotification = new MemberRemovedNotification($project,$member,$actor);
        foreach ($receivers as $receiver) {
            $receiver->notify($ownersNotification);
        }

        $memberNotification = new RemovedMemberNotification($project,$actor);
        $member->notify($memberNotification);
    }
}
