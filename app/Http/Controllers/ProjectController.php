<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Services\ProjectService;
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
}
