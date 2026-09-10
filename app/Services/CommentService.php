<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CommentService
{

    public function create(Task $task, User $user, array $data): Comment
    {
        $data['created_by'] = $user->id;
        $data['task_id'] = $task->id;
        return Comment::create($data);
    }

    public function list(Task $task) : Collection 
    {
        return $task->comments()->get();
    }

    public function update(Comment $comment, array $data)
    {
        $comment->update($data);
        return $comment->refresh();
    }

    public function delete(Comment $comment) : void
    {
         $comment->delete();
    }

}