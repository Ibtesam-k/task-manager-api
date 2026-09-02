<?php

namespace App\Policies;

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
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
       return $this->projectService->isMember($user,$project);
    }


    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $this->projectService->isOwner($user,$project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
         return $this->projectService->isOwner($user,$project);
    }

  
        /**
     * Determine whether the user can add member to the project .
     */
    public function addMember(User $user, Project $project): bool
        {
           return $this->projectService->isOwner($user,$project);
        }

     /**
     * Determine whether the user can remove member to the project .
     */
    public function removeMember(User $user, Project $project): bool
        {
           return $this->projectService->isOwner($user,$project);
        }

    public function listMembers(User $user, Project $project): bool 
    {
            return $this->projectService->isMember($user,$project);
    }

    public function createTask(User $user, Project $project) : bool
    {
        return $this->projectService->isMember($user,$project);
    }

}
