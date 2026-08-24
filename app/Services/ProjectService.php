<?php

namespace App\Services;

use App\Models\Project;
use App\Enums\ProjectRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
}