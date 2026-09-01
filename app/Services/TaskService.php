<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function create(Project $project, User  $user, array $data): Task
    {
        $data['created_by'] = $user->id;
        $data['project_id'] = $project->id;
        return Task::create($data);
    }
}