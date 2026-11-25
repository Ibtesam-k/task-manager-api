<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserService
{

    public function __construct(
        protected ProjectService $projectService,
        protected TaskService $taskService,
    ) {}

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        });
    }

    public function destroy(User $user): void
    {
        DB::transaction(function () use ($user) {
        $user = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();
        $projectIds = $this->projectService->getUserProjectsIds($user);
        $lockedProjects = Project::whereIn('id',$projectIds)
        ->orderBy('id')
        ->lockForUpdate()
        ->get();
        $projectsOwnersMap =  $this->projectService->getProjectsOwnersMap($projectIds);

        //validation
        $blockedProjects = [];
        foreach ($projectsOwnersMap as $projectId => $ownerIds) {
            if(in_array($user->id,$ownerIds) && count($ownerIds) === 1)
            {
                $blockedProjects[] = $projectId;
            }

        }

        if(!empty($blockedProjects))
        {
            $names = implode(', ',$lockedProjects->whereIn('id',$blockedProjects)->pluck('name')->toArray());
            throw new ConflictHttpException(
                    'You are the only owner of ('.$names.'). Promote another member to owner before deleting your account.'
                );
        }

        $this->taskService->unassignUserFromTasks($user);
        $user->projects()->detach();
        $user->tokens()->delete();
        $user->delete();
        });
    }

}