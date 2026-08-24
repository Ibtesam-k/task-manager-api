<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
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

});

Route::post('/users', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);