<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ActivityService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('viewActivities',$project);

        $data = $this->activityService->list($project);

        return response()->json([
            'message' => 'Activities retrieved successfully',
            'data' => $data,
        ]);
    }
}
