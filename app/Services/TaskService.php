<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskService
{
    public function create(Project $project, User  $user, array $data): Task
    {
        $data['created_by'] = $user->id;
        $data['project_id'] = $project->id;
        return Task::create($data);
    }

    public function update(Task $task, array $data)
    {
        $task->update($data);
        return $task->refresh();
    }
}