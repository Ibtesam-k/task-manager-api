<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class TaskService
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function create(Project $project, User  $user, array $data): Task
    {
        $data['created_by'] = $user->id;
        $data['project_id'] = $project->id;
        return Task::create($data);
    }

    public function update(Task $task, array $data) : Task
    {
        $task->update($data);
        return $task->refresh();
    }

    public function delete(Task $task) : void
    {
         $task->delete();
    }

    public function list(Project $project) : Collection 
    {
        return $project->tasks()->get();
    }

    public function assign(Task $task, ?int $assigneeId): Task
    {
        if (
            $assigneeId !== null &&
            ! $this->projectService->isUserMember(
                $assigneeId,
                $task->project
            )
        ) {
            throw new InvalidArgumentException(
                'The assignee must be a member of the project.'
            );
        }

        $task->update([
            'assignee_id' => $assigneeId,
        ]);

        return $task->refresh();
    }
}