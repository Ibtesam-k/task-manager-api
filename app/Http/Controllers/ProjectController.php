<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
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
}
