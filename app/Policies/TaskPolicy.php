<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectService;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct(
        protected ProjectService $projectService

    )
    {
    }

    
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        $project = $task->project;

        $role = $this->projectService->getUserRole($user, $project);

        if ($role === null) {
            return false;
        }

        return $role === ProjectRole::OWNER
            || $task->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
         return $this->projectService->isOwner($user,$task->project);
    }


}
