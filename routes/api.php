<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\IdeaController;

Route::middleware('auth:sanctum')->group(function () {
    // User endpoint
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Plans endpoints
    Route::apiResource('plans', PlanController::class);
    Route::post('/plans/{plan}/members', [PlanController::class, 'addMember']);
    Route::delete('/plans/{plan}/members/{user}', [PlanController::class, 'removeMember']);

    // Ideas endpoints - by plan
    Route::get('/plans/{plan}/ideas', [IdeaController::class, 'indexByPlan']);

    // Ideas endpoints - by group
    Route::get('/groups/{group}/ideas', [IdeaController::class, 'indexByGroup']);
    Route::post('/groups/{group}/ideas', [IdeaController::class, 'store']);

    // Ideas CRUD
    Route::get('/ideas/{idea}', [IdeaController::class, 'show']);
    Route::put('/ideas/{idea}', [IdeaController::class, 'update']);
    Route::delete('/ideas/{idea}', [IdeaController::class, 'destroy']);

    // Ideas actions
    Route::post('/ideas/{idea}/move', [IdeaController::class, 'move']);
    Route::post('/ideas/{idea}/complete', [IdeaController::class, 'complete']);
});
