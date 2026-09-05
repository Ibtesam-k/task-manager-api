<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/projects',[ProjectController::class,'index']);
    Route::post('/projects/{project}/members',[ProjectController::class,'addMember']);
    Route::delete('/projects/{project}/members',[ProjectController::class,'removeMember']);
    Route::get('/projects/{project}/members',[ProjectController::class,'listMembers']);
    Route::get('/projects/{project}',[ProjectController::class,'show']);
    Route::patch('/projects/{project}',[ProjectController::class,'update']);
    Route::delete('/projects/{project}',[ProjectController::class,'destroy']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}',[TaskController::class,'destroy']);
    Route::get('/projects/{project}/tasks',[TaskController::class,'index']);
    Route::get('/tasks/{task}',[TaskController::class,'show']);
    Route::patch('/tasks/{task}/assign',[TaskController::class,'assign']);
    Route::patch('/tasks/{task}/status',[TaskController::class,'changeStatus']);

});

Route::post('/users', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);