<?php

namespace Tests\Feature;

use App\Enums\ActivityType;
use App\Enums\ProjectRole;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Tests\TestCase;

class DeleteUserAccountTest extends TestCase
{

    public function test_user_cannot_delete_account_if_they_are_the_sole_owner_of_a_project(): void
    {
        // Arrange
        $project = Project::factory()->create();
        $ownerA = User::factory()->create();

        $project->users()->attach($ownerA->id, [
            'role' => ProjectRole::OWNER->value,
        ]);

        // Act
        $response = $this->actingAs($ownerA)->deleteJson('/api/users');

        // Assert
        $response->assertConflict();

        $this->assertNotSoftDeleted('users', [
            'id' => $ownerA->id,
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $ownerA->id,
            'role' => ProjectRole::OWNER->value,
        ]);
    }


    public function test_user_can_delete_account_if_there_are_other_owners_of_their_projects(): void
    {
        // Arrange
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $ownerA = User::factory()->create();
        $ownerB= User::factory()->create();

        $project1->users()->attach($ownerA->id, [
            'role' => ProjectRole::OWNER->value,
        ]);
        $project1->users()->attach($ownerB->id, [
            'role' => ProjectRole::OWNER->value,
        ]);

        $project2->users()->attach($ownerA->id, [
            'role' => ProjectRole::OWNER->value,
        ]);
        $project2->users()->attach($ownerB->id, [
            'role' => ProjectRole::OWNER->value,
        ]);


        // Act
        $response = $this->actingAs($ownerA)->deleteJson('/api/users');

        // Assert
        $response->assertSuccessful();

        $this->assertSoftDeleted('users', [
            'id' => $ownerA->id,
        ]);

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project1->id,
            'user_id' => $ownerA->id,
            'role' => ProjectRole::OWNER->value,
        ]);

         $this->assertDatabaseMissing('project_user', [
            'project_id' => $project2->id,
            'user_id' => $ownerA->id,
            'role' => ProjectRole::OWNER->value,
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project1->id,
            'user_id' => $ownerB->id,
            'role' => ProjectRole::OWNER->value,
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project2->id,
            'user_id' => $ownerB->id,
            'role' => ProjectRole::OWNER->value,
        ]);
    }

    public function test_user_can_delete_account_if_they_are_a_project_member(): void
    {
        // Arrange
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $owner = User::factory()->create();


        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);

        $project->users()->attach($member->id, [
            'role' => ProjectRole::MEMBER->value,
        ]);

        // Act
        $response = $this->actingAs($member)->deleteJson('/api/users');

        // Assert
        $response->assertSuccessful();

        $this->assertSoftDeleted('users', [
                'id' => $member->id,
            ]);

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => ProjectRole::MEMBER->value,
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role' => ProjectRole::OWNER->value,
        ]);
    }

    public function test_non_auth_user_cannot_delete_account(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->deleteJson('/api/users');

        // Assert
        $response->assertUnauthorized();

        $this->assertNotSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_deleted_user_assigned_tasks_are_unassigned() : void
    {
        // Arrange
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $owner = User::factory()->create();

        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);

        $project->users()->attach($member->id, [
            'role' => ProjectRole::MEMBER->value,
        ]);

        $tasks = Task::factory()->count(3)->create([
            'project_id' => $project->id,
            'assignee_id' => $member->id,
            'created_by' => $owner->id,
        ]);

        // Act
        $response = $this->actingAs($member)->deleteJson('/api/users');

        // Assert
        $response->assertSuccessful();

        $this->assertSoftDeleted('users', [
            'id' => $member->id,
        ]);

        foreach ($tasks as $task) {
            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'assignee_id' => null,
                'created_by' => $owner->id,
            ]);
        }


    }

    public function test_deleted_user_created_tasks_are_not_deleted() : void
    {
        // Arrange
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $owner = User::factory()->create();

        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);


        $project->users()->attach($member->id, [
            'role' => ProjectRole::MEMBER->value,
        ]);

        $tasks = Task::factory()->count(3)->create([
            'project_id' => $project->id,
            'assignee_id' => $owner->id,
            'created_by' => $member->id,
        ]);

        // Act
        $response = $this->actingAs($member)->deleteJson('/api/users');

        // Assert
        $response->assertSuccessful();

         $this->assertSoftDeleted('users', [
                'id' => $member->id,
            ]);

        $this->assertSame(3, Task::where('created_by', $member->id)->count(), 'Tasks created by the deleted user should not be deleted.');
        foreach ($tasks as $task) {
            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'assignee_id' => $owner->id,
                'created_by' => $member->id,
            ]);
        }
    }

    public function test_comments_created_by_deleted_user_are_not_deleted() : void
    {
        // Arrange
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $owner = User::factory()->create();

        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);


        $project->users()->attach($member->id, [
            'role' => ProjectRole::MEMBER->value,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assignee_id' => $owner->id,
            'created_by' => $owner->id,
        ]);

        $comments = Comment::factory()->count(3)->create([
            'task_id' => $task->id,
            'created_by' => $member->id,
            'body' => 'Sample comment',
        ]);

        // Act
        $response = $this->actingAs($member)->deleteJson('/api/users');

        // Assert
        $response->assertSuccessful();

         $this->assertSoftDeleted('users', [
                'id' => $member->id,
            ]);

        foreach ($comments as $comment) {
            $this->assertDatabaseHas('comments', [
                'id' => $comment->id,
                'task_id' => $task->id,
                'created_by' => $member->id,
                'body' => 'Sample comment',
            ]);
        }
    }

    public function test_activities_are_preserved_after_actor_account_deletion() : void
    {
        // Arrange
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $owner = User::factory()->create();

        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);


        $project->users()->attach($member->id, [
            'role' => ProjectRole::MEMBER->value,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assignee_id' => null,
            'created_by' => $member->id,
        ]);

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'created_by' => $member->id,
            'body' => 'Sample comment',
        ]);

        $createTaskActivity =Activity::create([
                'project_id' => $project->id,
                'actor_id' => $member->id,
                'trackable_type' => Task::class,
                'trackable_id' => $task->id,
                'type' => ActivityType::TASK_CREATED->value,
                'metadata' => [],
                'created_at'=>now()
        ]);


        $updateTaskActivity =Activity::create([
                'project_id' => $project->id,
                'actor_id' => $member->id,
                'trackable_type' => Task::class,
                'trackable_id' => $task->id,
                'type' => ActivityType::TASK_ASSIGNED->value,
                'metadata' => [
                'old_assignee_id' => null,
                'new_assignee_id' => $member->id,],
                'created_at'=>now()
            ]);


        $createdCommentActivity =Activity::create([
                'project_id' => $project->id,
                'actor_id' => $member->id,
                'trackable_type' => Comment::class,
                'trackable_id' => $comment->id,
                'type' => ActivityType::COMMENT_CREATED->value,
                'metadata' => [],
                'created_at'=>now()
        ]);

        // Act
        $response = $this->actingAs($member)->deleteJson('/api/users');

        // Assert
        $response->assertSuccessful();

         $this->assertSoftDeleted('users', [
                'id' => $member->id,
            ]);

        $this->assertDatabaseHas('activities', [
                'id' => $createTaskActivity->id,
                'project_id' => $project->id,
                'actor_id' => $member->id,
                'trackable_type' => Task::class,
                'trackable_id' => $task->id,
                'type' => ActivityType::TASK_CREATED->value,
                'metadata' =>$this->castAsJson([]),
            ]);

    $this->assertDatabaseHas('activities', [
                'id' => $updateTaskActivity->id,
                'project_id' => $project->id,
                'actor_id' => $member->id,
                'trackable_type' => Task::class,
                'trackable_id' => $task->id,
                'type' => ActivityType::TASK_ASSIGNED->value,
                'metadata' => $this->castAsJson([
                    'old_assignee_id' => null,
                    'new_assignee_id' => $member->id,
                ]),
            ]);


    $this->assertDatabaseHas('activities', [
                'id' => $createdCommentActivity->id,
                'project_id' => $project->id,
                'actor_id' => $member->id,
                'trackable_type' => Comment::class,
                'trackable_id' => $comment->id,
                'type' => ActivityType::COMMENT_CREATED->value,
                'metadata' => $this->castAsJson([]),
            ]);

    $this->assertSame(
        3,
        Activity::where('actor_id', $member->id)->count()
    );
    }

    public function test_notifications_are_preserved_after_recipient_account_deletion() : void
    {
        // Arrange
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $owner = User::factory()->create();

        $project->users()->attach($owner->id, [
            'role' => ProjectRole::OWNER->value,
        ]);


        $project->users()->attach($member->id, [
            'role' => ProjectRole::MEMBER->value,
        ]);

        $task = Task::factory()->create([
            'created_by' => $owner->id,
            'assignee_id' => $member->id,
            'project_id' => $project->id,
        ]);

        $member->notify(new TaskAssignedNotification($task, $owner));
        $notification = $member->notifications()->first();
        // Act
        $response = $this->actingAs($member)->deleteJson('/api/users');

        // Assert
        $response->assertSuccessful();

        $this->assertSoftDeleted('users', [
                'id' => $member->id,
            ]);

        $this->assertDatabaseHas('notifications', [
            'id'=> $notification->id,
            'notifiable_id' => $member->id,
            'notifiable_type' => User::class,
            'type' => TaskAssignedNotification::class,
        ]);
    }

    public function test_user_token_is_revoked_after_account_deletion() : void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        // Act
        $response = $this->withToken($token->plainTextToken)->deleteJson('/api/users');

        // Assert
        $response->assertSuccessful();

        $this->assertSoftDeleted('users', [
                'id' => $user->id,
            ]);


        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

}
