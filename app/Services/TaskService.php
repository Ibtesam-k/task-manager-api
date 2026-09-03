<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
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
}