<?php

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'homepage', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'view'])->name('profile');
    Route::inertia('/plans', 'planlist')->name('plans');
    Route::inertia('/plans/{id}', 'plan')->name('plan.view');
});

require __DIR__ . '/settings.php';
