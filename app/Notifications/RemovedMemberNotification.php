<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemovedMemberNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Project $project,public User $actor)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return  [
            'message' => 'You were removed from project "' . $this->project->title .
                '" by ' . $this->actor->name . '.',
            'project_uuid' => $this->project->uuid,
            'type' => NotificationType::MEMBER_REMOVED->value,
        ];
    }
}
