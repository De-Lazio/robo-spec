<?php

namespace App\Providers;

use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectInvitationRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectMembershipRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Projects\Repositories\EloquentProjectActivityRepository;
use App\Domain\Projects\Repositories\EloquentProjectInvitationRepository;
use App\Domain\Projects\Repositories\EloquentProjectMembershipRepository;
use App\Domain\Projects\Repositories\EloquentProjectRepository;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
        $this->app->bind(ProjectMembershipRepositoryInterface::class, EloquentProjectMembershipRepository::class);
        $this->app->bind(ProjectActivityRepositoryInterface::class, EloquentProjectActivityRepository::class);
        $this->app->bind(ProjectInvitationRepositoryInterface::class, EloquentProjectInvitationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
