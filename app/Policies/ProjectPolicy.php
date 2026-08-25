<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function __construct(
        protected ProjectService $projectService
    ) {}
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }

        /**
     * Determine whether the user can add member to the project .
     */
    public function addMember(User $user, Project $project): bool
        {
           return $this->isOwner($user,$project);
        }

     /**
     * Determine whether the user can remove member to the project .
     */
    public function removeMember(User $user, Project $project): bool
        {
           return $this->isOwner($user,$project);
        }

    public function listMembers(User $user, Project $project): bool 
    {
            return $this->isMember($user,$project);
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
