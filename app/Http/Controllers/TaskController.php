<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('viewTasks', $project);
        $data = $this->taskService->list($project);
        
        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'data' => $data,
        ]);

    }
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

    public function destroy(Task $task)
    {
        $this->authorize('delete',$task);
        $this->taskService->delete($task);
        return response()->json([
                'message' => 'Task deleted successfully'
        ]);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return response()->json([
            'message' => 'Task retrieved successfully',
            'data' => $task,
        ]);    }

    public function assign(AssignTaskRequest $request, Task $task)
    {
        $this->authorize('assign', $task);

        $assigneeId = $request->validated('assignee_id');

        $task = $this->taskService->assign($task, $assigneeId);

        return response()->json([
            'message' => $assigneeId === null
                ? 'Task unassigned successfully'
                : 'Task assigned successfully',
            'data' => $task,
        ]);
    }

    public function changeStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $this->authorize('changeStatus', $task);
        
        $task = $this->taskService->update(
            $task,
            $request->validated()
        );

        return response()->json([
            'message' => 'Task status updated successfully',
            'data' => $task,
        ]);

    }
}
