<?php

namespace App\Providers;

use App\Domain\Components\Contracts\ComponentCategoryRepositoryInterface;
use App\Domain\Components\Contracts\ComponentRepositoryInterface;
use App\Domain\Components\Repositories\EloquentComponentCategoryRepository;
use App\Domain\Components\Repositories\EloquentComponentRepository;
use App\Domain\Integrations\GitHub\Clients\GitHubClientInterface;
use App\Domain\Integrations\GitHub\Clients\HttpGitHubClient;
use App\Domain\Integrations\GitHub\Contracts\GithubLinkRepositoryInterface;
use App\Domain\Integrations\GitHub\Repositories\EloquentGithubLinkRepository;
use App\Domain\Organizations\Contracts\OrganizationInvitationRepositoryInterface;
use App\Domain\Organizations\Contracts\OrganizationMembershipRepositoryInterface;
use App\Domain\Organizations\Contracts\OrganizationRepositoryInterface;
use App\Domain\Organizations\Repositories\EloquentOrganizationInvitationRepository;
use App\Domain\Organizations\Repositories\EloquentOrganizationMembershipRepository;
use App\Domain\Organizations\Repositories\EloquentOrganizationRepository;
use App\Domain\Projects\Contracts\ProjectActivityRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectInvitationRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectMembershipRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Projects\Repositories\EloquentProjectActivityRepository;
use App\Domain\Projects\Repositories\EloquentProjectInvitationRepository;
use App\Domain\Projects\Repositories\EloquentProjectMembershipRepository;
use App\Domain\Projects\Repositories\EloquentProjectRepository;
use App\Domain\Requirements\Contracts\RequirementsDocumentRepositoryInterface;
use App\Domain\Requirements\Repositories\EloquentRequirementsDocumentRepository;
use App\Domain\Resources\Contracts\ResourceRepositoryInterface;
use App\Domain\Resources\Repositories\EloquentResourceRepository;
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
        $this->app->bind(RequirementsDocumentRepositoryInterface::class, EloquentRequirementsDocumentRepository::class);
        $this->app->bind(ResourceRepositoryInterface::class, EloquentResourceRepository::class);
        $this->app->bind(GithubLinkRepositoryInterface::class, EloquentGithubLinkRepository::class);
        $this->app->bind(GitHubClientInterface::class, HttpGitHubClient::class);
        $this->app->bind(OrganizationRepositoryInterface::class, EloquentOrganizationRepository::class);
        $this->app->bind(OrganizationMembershipRepositoryInterface::class, EloquentOrganizationMembershipRepository::class);
        $this->app->bind(OrganizationInvitationRepositoryInterface::class, EloquentOrganizationInvitationRepository::class);
        $this->app->bind(ComponentCategoryRepositoryInterface::class, EloquentComponentCategoryRepository::class);
        $this->app->bind(ComponentRepositoryInterface::class, EloquentComponentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
