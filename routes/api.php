<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\IdeaController;
use App\Http\Controllers\Api\ProfileController;

// API routes with Sanctum token authentication
Route::middleware([
    'auth:sanctum',
    'verified'
])->group(function () {
    // User endpoint
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user');

    // Profile endpoints
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Plans endpoints
    Route::apiResource('plans', PlanController::class);
    Route::post('/plans/{plan}/members', [PlanController::class, 'addMember'])->name('plans.addMember');
    Route::delete('/plans/{plan}/members/{user}', [PlanController::class, 'removeMember'])->name('plans.removeMember');

    // Ideas endpoints - by plan
    Route::get('/plans/{plan}/ideas', [IdeaController::class, 'indexByPlan'])->name('plans.ideas.index');

    // Ideas endpoints - by group
    Route::get('/idea-groups/{group}/ideas', [IdeaController::class, 'indexByGroup'])->name('groups.ideas.index');
    Route::post('/idea-groups/{group}/ideas', [IdeaController::class, 'store'])->name('groups.ideas.store');
    
    // Idea Groups CRUD
    Route::get('/plans/{plan}/groups', [IdeaController::class, 'indexGroups'])->name('plans.groups.index');
    Route::post('/plans/{plan}/groups', [IdeaController::class, 'storeGroup'])->name('plans.groups.store');
    Route::put('/idea-groups/{group}', [IdeaController::class, 'updateGroup'])->name('groups.update');
    Route::delete('/idea-groups/{group}', [IdeaController::class, 'destroyGroup'])->name('groups.destroy');

    // Ideas CRUD
    Route::get('/ideas/{idea}', [IdeaController::class, 'show'])->name('ideas.show');
    Route::put('/ideas/{idea}', [IdeaController::class, 'update'])->name('ideas.update');
    Route::delete('/ideas/{idea}', [IdeaController::class, 'destroy'])->name('ideas.destroy');

    // Ideas actions
    Route::post('/ideas/{idea}/move', [IdeaController::class, 'move'])->name('ideas.move');
    Route::post('/ideas/{idea}/complete', [IdeaController::class, 'complete'])->name('ideas.complete');
});
