<?php

namespace App\Services;

use App\Enums\ProjectRole;
use App\Events\MemberAdded;
use App\Events\MemberRemoved;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectService
{
    public function create(array $data, int $userId): Project
    {
        return DB::transaction(function () use ($data, $userId) {
            $project = Project::create($data);

            $project->users()->attach($userId, [
                'role' => ProjectRole::OWNER->value,
            ]);

            return $project;
        });
    }



    public function getUserProjects(User $user)
    {
        return $user->projects()->get();
    }

    public function addMember(Project $project, int $userId, User $actor) :void
    {
        DB::transaction(function () use ($project, $userId, $actor)
        {
            $lockedUsers = User::whereIn('id',[$userId,$actor->id])->orderBy('id')
            ->lockForUpdate()
            ->get();

            if ($lockedUsers->count() !== count(array_unique([$userId, $actor->id]))) {
                throw new NotFoundHttpException('User not found.');
            }

            $project = Project::whereKey($project->id)
                            ->lockForUpdate()
                            ->firstOrFail();

            if ($project->users()->where('users.id', $userId)->exists()) {
                        throw new ConflictHttpException(
                            'User is already a member of this project.'
                        );
                    }
            $project->users()->attach($userId,
            [
                'role'=>ProjectRole::MEMBER->value
            ]);
        });

        MemberAdded::dispatch($project, $actor, $userId);
    }

    public function removeMember(Project $project, int $userId,  User $actor): void
    {
        DB::transaction(function () use($project,$userId,$actor){

            $lockedUsers = User::whereIn('id',[$userId,$actor->id])->orderBy('id')
            ->lockForUpdate()
            ->get();

            if ($lockedUsers->count() !== count(array_unique([$userId, $actor->id]))) {
                throw new NotFoundHttpException('User not found.');
            }

            $project = Project::whereKey($project->id)
                            ->lockForUpdate()
                            ->firstOrFail();

            $member = $project->users()
                            ->withPivot('role')
                            ->find($userId);


            if (!$member) {
                throw new NotFoundHttpException(
                    'User is not a member of this project.'
                );
            }

            if ($member->pivot->role === ProjectRole::OWNER->value) {
                throw new ConflictHttpException(
                    'The project owner cannot be removed.'
                );
            }

            $project->users()->detach($userId);
            }) ;
        
        MemberRemoved::dispatch($project, $actor, $userId);

    }

    
    public function listMembers(Project $project)
    {
        return $project->users()->select('users.id','users.name')->get();
    }

    public function update(Project $project , array $data )
    {
        $project->update($data);
        return $project->refresh();
    }

    public function delete(Project $project) : void
    {
        DB::transaction(function () use ($project){
            $project->tasks()->delete();
            $project->delete();
        });
    }

    public function isOwner(User $user, Project $project): bool
    {
        return $this->getUserRole($user, $project) === ProjectRole::OWNER;
    }

    public function isMember(User $user, Project $project): bool
    {
        return $this->getUserRole($user, $project) !== null;
    }

    public function getUserRole(User $user, Project $project) : ?ProjectRole
    {
        $role = $project->users()->where('users.id',$user->id)->value('project_user.role');
        return $role ? ProjectRole::from($role) : null;
    }

    public function isUserMember(int $userId, Project $project): bool
    {
        return $project->users()
            ->where('users.id', $userId)
            ->exists();
    }

    public function getProjectOwners(Project $project) : Collection
    {
        return $project->users()
            ->withPivot('role')
            ->where('project_user.role', ProjectRole::OWNER->value)
            ->get();   
     }

     public function updateUserRole(Project $project,User $actor, User $target, ProjectRole $role) : void
    {

        if ($target->id === $actor->id) {
            throw new ConflictHttpException(
                'You cannot change your own project role.'
            );
        }
        DB::transaction(function () use ($project,$target,$actor,$role) {

             $lockedUsers = User::whereIn('id',[$target->id,$actor->id])->orderBy('id')
            ->lockForUpdate()
            ->get();

            if ($lockedUsers->count() !== count(array_unique([$target->id, $actor->id]))) {
                throw new NotFoundHttpException('User not found.');
            }
            $project = Project::whereKey($project->id)
                            ->lockForUpdate()
                            ->firstOrFail();

            $currentRole = $this->getUserRole($target, $project);

            if($currentRole === null)
            {
                throw new NotFoundHttpException(
                    'User is not a member of this project.'
                );
            }

            if($currentRole === $role)
            {
                throw new ConflictHttpException(
                    'User already has this role.'
                );
            }

            if (
                $currentRole === ProjectRole::OWNER &&
                $role === ProjectRole::MEMBER &&
                !$this->hasOtherOwners($project, $target)
            ) {
                throw new ConflictHttpException(
                    'You cannot demote the last owner of this project.'
                );
            }

            $project->users()->updateExistingPivot(
                $target->id,
                ['role' => $role->value]
            );
            });
        
    }

    public function hasOtherOwners(Project $project, User $owner) : bool
    {
      return $project->users()
      ->where('users.id','!=',$owner->id)
      ->wherePivot('role',ProjectRole::OWNER->value)->exists();
    }

    public function leave(User $user, Project $project) : int
    {
      return  DB::transaction(function () use ($project, $user) {

        $user = User::whereKey($user->id)
            ->lockForUpdate()
            ->firstOrFail();

        $project = Project::whereKey($project->id)
            ->lockForUpdate()
            ->firstOrFail();

            $role = $this->getUserRole($user, $project);
            if($role === null)
            {
                    throw new NotFoundHttpException(
                    'You are not a member of this project.'
                ); 
            }
            if($role === ProjectRole::OWNER && !$this->hasOtherOwners($project,$user))
            {
                throw new ConflictHttpException(
                    'You are the only owner of this project. Promote another member to owner before leaving.'
                );    
            }

            return $project->users()->detach($user->id);
        });
            
    }

        public function getUserProjectsIds(User $user): array
        {
            return $user->projects()
                ->orderBy('projects.id')
                ->pluck('projects.id')
                ->all();
        }

        public function getProjectsOwnersMap(array $projectIds = []) : array
        {
           return  DB::table('project_user')
                    ->whereIn('project_id', $projectIds)
                    ->where('role', ProjectRole::OWNER->value)
                    ->get(['project_id', 'user_id'])
                    ->groupBy('project_id')
                    ->map(fn ($owners) => $owners->pluck('user_id')->toArray())
                    ->toArray();
        }
}