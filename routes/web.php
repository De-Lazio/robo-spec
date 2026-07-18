<?php

use App\Http\Controllers\GitHubController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\RequirementsDocumentController;
use App\Http\Controllers\ResourceController;
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

        Route::get('/projects/{project}/resources', [ResourceController::class, 'index'])->name('projects.resources.index');
        Route::post('/projects/{project}/resources', [ResourceController::class, 'store'])->name('projects.resources.store');

        Route::scopeBindings()->group(function (): void {
            Route::put('/projects/{project}/members/{member}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
            Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

            Route::get('/projects/{project}/resources/{resource}/download', [ResourceController::class, 'download'])->name('projects.resources.download');
            Route::get('/projects/{project}/resources/{resource}/preview', [ResourceController::class, 'preview'])->name('projects.resources.preview');
            Route::delete('/projects/{project}/resources/{resource}', [ResourceController::class, 'destroy'])->name('projects.resources.destroy');
        });

        Route::get('/projects/{project}/github', [GitHubController::class, 'show'])->name('projects.github.show');
        Route::post('/projects/{project}/github', [GitHubController::class, 'store'])->name('projects.github.store');
        Route::post('/projects/{project}/github/sync', [GitHubController::class, 'sync'])->name('projects.github.sync');
        Route::delete('/projects/{project}/github', [GitHubController::class, 'destroy'])->name('projects.github.destroy');

        Route::get('/projects/{project}/cdc', [RequirementsDocumentController::class, 'show'])->name('projects.requirements.show');
        Route::get('/projects/{project}/cdc/edit', [RequirementsDocumentController::class, 'edit'])->name('projects.requirements.edit');
        Route::put('/projects/{project}/cdc/steps/{step}', [RequirementsDocumentController::class, 'saveStep'])->whereNumber('step')->name('projects.requirements.steps.save');
        Route::put('/projects/{project}/cdc/draft', [RequirementsDocumentController::class, 'saveDraft'])->name('projects.requirements.draft.save');
        Route::post('/projects/{project}/cdc/publish', [RequirementsDocumentController::class, 'publish'])->name('projects.requirements.publish');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
