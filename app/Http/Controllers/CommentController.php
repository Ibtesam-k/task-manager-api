<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use App\Services\CommentService;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function store(StoreCommentRequest $request, Task $task)
    {
        $this->authorize('createComment',$task);
        $comment = $this->commentService->create($task,$request->user(),$request->validated());
        
        return response()->json([
            'message' => 'Comment created successfully',
            'data' => $comment,
        ], 201);
    }

    public function index(Task $task)
    {
        $this->authorize('viewComments',$task);

        $data = $this->commentService->list($task);

        return response()->json([
            'message' => 'Comments retrieved successfully',
            'data' => $data,
        ]);

    }

    public function update(UpdateCommentRequest $request,Comment $comment)
    {
        $this->authorize('update',$comment);

        $comment = $this->commentService->update($comment,$request->validated());

        return response()->json([
            'message' => 'Comment updated successfully',
            'data' => $comment,
        ]);
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete',$comment);

        $this->commentService->delete($comment);
        return response()->json([
                'message' => 'Comment deleted successfully'
        ]);
    }
}
