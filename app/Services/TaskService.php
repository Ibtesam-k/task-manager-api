<?php

namespace App\Services;

use App\Events\TaskAssignmentChanged;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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
        $task =  Task::create($data);
        TaskCreated::dispatch($task);
        return $task;
    }

    public function update(Task $task, array $data, User $user): Task
    {
        $oldStatus = $task->status;

        $task->update($data);

        if (
            array_key_exists('status', $data)
            && $oldStatus !== $task->status
        ) {
            TaskStatusChanged::dispatch(
                $task,
                $user,
                [
                    'old_status' => $oldStatus->value,
                    'new_status' => $task->status->value,
                ]
            );
        }

        return $task->refresh();
    }

    public function delete(Task $task) : void
    {
        DB::transaction(function () use ($task){
         $task->comments()->delete();
         $task->delete();
        });
    }

    public function list(Project $project) : Collection 
    {
        return $project->tasks()->get();
    }

    public function assign(Task $task, User $user, ?int $assigneeId): Task
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

        $oldAssigneeId = $task->assignee_id;

        $task->update([
            'assignee_id' => $assigneeId,
        ]);

        if ($oldAssigneeId !== $assigneeId) {
            TaskAssignmentChanged::dispatch(
                $task,
                $user,
                $oldAssigneeId,
                $assigneeId
            );
        }

        return $task->refresh();
    }
}