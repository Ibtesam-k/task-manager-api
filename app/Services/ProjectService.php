<?php

namespace App\Services;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
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

    public function addMember(Project $project, int $userId)
    {
        if ($project->users()->where('users.id', $userId)->exists()) {
            throw new ConflictHttpException(
                'User is already a member of this project.'
            );
        }
        $project->users()->attach($userId, 
        [
            'role'=>ProjectRole::MEMBER->value
        ]);

    }

    public function removeMember(Project $project, int $userId): void
    {
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

    public function delete(Project $project)
    {
        $project->delete();
        //ToDO: how this affects tasks
    }

}