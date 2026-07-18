<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/invitations/{token}', [ProjectMemberController::class, 'acceptInvitation'])
    ->name('invitations.accept')
    ->middleware('throttle:20,1');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [ProjectController::class, 'dashboard'])->name('dashboard');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

    Route::middleware('project.member')->group(function (): void {
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project}/settings', [ProjectController::class, 'settings'])->name('projects.settings');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::patch('/projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
        Route::patch('/projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

        Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index'])->name('projects.members.index');
        Route::post('/projects/{project}/members/invitations', [ProjectMemberController::class, 'storeInvitation'])->name('projects.members.invitations.store');

        Route::scopeBindings()->group(function (): void {
            Route::put('/projects/{project}/members/{member}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
            Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');
        });
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
