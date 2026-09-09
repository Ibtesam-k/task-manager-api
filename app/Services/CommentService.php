<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class CommentService
{

    public function create(Task $task, User $user, array $data): Comment
    {
        $data['created_by'] = $user->id;
        $data['task_id'] = $task->id;
        return Comment::create($data);
    }
}