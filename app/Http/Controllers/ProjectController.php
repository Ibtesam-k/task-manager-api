<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\ProjectMemberRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
     public function __construct(
        protected ProjectService $projectService
    ) {}

    function store(StoreProjectRequest  $request)
    {
        $project = $this->projectService->create(
            $request->validated(),
            Auth::id()
        );

        return response()->json($project, 201);
    }
    
    public function index(Request $request)
    {
        return $this->projectService->getUserProjects(
            $request->user()
        );
    }

    public function addMember(ProjectMemberRequest $request,Project $project) {
            $this->authorize('addMember', $project);

            $this->projectService->addMember(
                $project,
                $request->validated('userId')
            );

            return response()->json([
                'message' => 'Member added successfully',
            ], 201);
        }
    
        public function removeMember(ProjectMemberRequest $request, Project $project)
        {
            $this->authorize('removeMember', $project);

            $this->projectService->removeMember($project,$request->validated('userId'));

            return response()->json(['message'=>'Member removed successfully'], 200);
        }
}
