<?php

namespace App\Listeners;

use App\Events\MemberAdded;
use App\Models\User;
use App\Notifications\AddedMemberNotification;
use App\Notifications\MemberAddedNotification;
use App\Services\ProjectService;

class NotifyMemberAdded
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
    public function handle(MemberAdded $event): void
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

        $ownersNotification = new MemberAddedNotification($project,$member,$actor);
        foreach ($receivers as $receiver) {
            $receiver->notify($ownersNotification);
        }

        $memberNotification = new AddedMemberNotification($project,$actor);
        $member->notify($memberNotification);
    }
}
