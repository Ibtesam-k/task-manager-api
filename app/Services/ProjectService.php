<?php

namespace App\Services;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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

}