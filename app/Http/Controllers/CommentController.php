<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Models\Task;
use App\Services\CommentService;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function store(CreateCommentRequest $request, Task $task)
    {
        $this->authorize('createComment',$task);
        $comment = $this->commentService->create($task,$request->user(),$request->validated());
        
        return response()->json([
            'message' => 'Comment created successfully',
            'data' => $comment,
        ], 201);
    }
}
