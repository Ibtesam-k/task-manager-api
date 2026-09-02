<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {

    }

    
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        if (!$this->isMember($user, $task->project)) {
        return false;
        }
        return $this->isOwner($user,$task->project) || ($task->created_by === $user->id);
    }


    private function isOwner(User $user, Project $project): bool
    {
        return $project->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', ProjectRole::OWNER->value)
            ->exists();
    }

    private function isMember(User $user, Project $project): bool
    {
        return $project->users()
            ->where('users.id', $user->id)
            ->exists();
    }

}
