<?php

namespace Tests\Feature;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeaveProjectTest extends TestCase
{


    use RefreshDatabase;


// public function test_owner_can_leave_when_another_owner_exists(): void
//     {
//         // Arrange
//         $project = Project::factory()->create();

//         $ownerA = User::factory()->create();
//         $ownerB = User::factory()->create();

//         $project->users()->attach($ownerA->id, [
//             'role' => ProjectRole::OWNER->value,
//         ]);

//         $project->users()->attach($ownerB->id, [
//             'role' => ProjectRole::OWNER->value,
//         ]);

//         // Act
//         $projectService = app(ProjectService::class);

//         $projectService->leave($ownerA, $project);

//         // Assert
//         $this->assertDatabaseMissing('project_user', [
//             'project_id' => $project->id,
//             'user_id' => $ownerA->id,
//         ]);

//         $this->assertDatabaseHas('project_user', [
//             'project_id' => $project->id,
//             'user_id' => $ownerB->id,
//             'role' => ProjectRole::OWNER->value,
//         ]);
//     }


    public function test_owner_can_leave_when_another_owner_exists(): void
    {
        // Arrange
        $project = Project::factory()->create();

        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();

        $project->users()->attach($ownerA->id, [
            'role' => ProjectRole::OWNER->value,
        ]);

        $project->users()->attach($ownerB->id, [
            'role' => ProjectRole::OWNER->value,
        ]);

        // Act
        $response = $this->actingAs($ownerA)
        ->deleteJson("/api/projects/{$project->uuid}/leave");

        // Assert
        $response->assertSuccessful();

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $ownerA->id,
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $ownerB->id,
            'role' => ProjectRole::OWNER->value,
        ]);
    }

    public function test_only_owner_cannot_leave_project(): void
    {
        // Arrange
        $project = Project::factory()->create();
        $owner = User::factory()->create();

        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);

        // Act
        $response = $this->actingAs($owner)
            ->deleteJson("/api/projects/{$project->uuid}/leave");

        // Assert
        $response->assertConflict();
        //$response->assertStatus(409);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role' => ProjectRole::OWNER->value,
        ]);
    }

    public function test_member_can_leave_project(): void
    {
        // Arrange
        $project = Project::factory()->create();
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);
        $project->users()->attach($member->id, [
            'role' => ProjectRole::MEMBER->value,
        ]);

        // Act
        $response = $this->actingAs($member)
            ->deleteJson("/api/projects/{$project->uuid}/leave");

        // Assert
        $response->assertSuccessful();
        
        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_non_member_cannot_leave_project(): void
    {
        // Arrange
        $project = Project::factory()->create();
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);


        // Act
        $response = $this->actingAs($user)
            ->deleteJson("/api/projects/{$project->uuid}/leave");

        // Assert
        $response->assertNotFound();
        
        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role' => ProjectRole::OWNER->value,
        ]);
    }


    public function test_non_auth_user_cannot_leave_project(): void
    {
        // Arrange
        $project = Project::factory()->create();

        // Act
        $response = $this->deleteJson("/api/projects/{$project->uuid}/leave");

        // Assert
        $response->assertUnauthorized();
        

    }
}
