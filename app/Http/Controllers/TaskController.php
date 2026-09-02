<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->authorize('createTask', $project);

        $task = $this->taskService->create(
            $project,
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task,
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $task = $this->taskService->update($task,$request->validated());
        
        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task,
        ]);

    }
}
