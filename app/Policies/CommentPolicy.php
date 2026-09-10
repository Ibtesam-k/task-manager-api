<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Comment;
use App\Models\User;
use App\Services\ProjectService;

class CommentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct(
                protected ProjectService $projectService
    )
    {
        //
    }

    public function update(User $user, Comment $comment) : bool
    {
        $project = $comment->task->project;
        $isMember = $this->projectService->isMember($user, $project);
        return $isMember && $comment->created_by === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        $project = $comment->task->project;

        $role = $this->projectService->getUserRole($user, $project);

        if ($role === null) {
            return false;
        }

        return $role === ProjectRole::OWNER
            || $comment->created_by === $user->id;
    }
}
