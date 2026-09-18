<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;


class ActivityService
{

    public function record(
        ActivityType $type,
        Project $project,
        User $actor,
        Model $trackable,
        array $metadata = []
        ): Activity 
        {
            return Activity::create([
                'project_id' => $project->id,
                'actor_id' => $actor->id,
                'trackable_type' => $trackable::class,
                'trackable_id' => $trackable->id,
                'type' => $type,
                'metadata' => $metadata,
                'created_at'=>now()
            ]);
    }

}