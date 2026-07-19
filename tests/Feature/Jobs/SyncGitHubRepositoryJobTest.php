<?php

namespace Tests\Feature\Jobs;

use App\Domain\Integrations\GitHub\Clients\GitHubClientInterface;
use App\Domain\Integrations\GitHub\Contracts\GithubLinkRepositoryInterface;
use App\Domain\Integrations\GitHub\Enums\SyncStatus;
use App\Jobs\SyncGitHubRepositoryJob;
use App\Models\GithubRepository;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncGitHubRepositoryJobTest extends TestCase
{
    use RefreshDatabase;

    private function makeLink(): GithubRepository
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        return GithubRepository::query()->create([
            'project_id' => $project->getKey(),
            'provider' => 'github',
            'owner' => 'laravel',
            'repository' => 'laravel',
            'url' => 'https://github.com/laravel/laravel',
            'default_branch' => 'main',
            'visibility' => 'public',
            'last_synced_at' => now()->subDay(),
            'sync_status' => SyncStatus::Pending,
            'metadata' => ['description' => null, 'stars' => 0, 'forks' => 0, 'open_issues' => 0, 'language' => null, 'branches' => [], 'commits' => [], 'error' => null],
        ]);
    }

    public function test_a_successful_run_updates_metadata_and_marks_synced(): void
    {
        Http::fake([
            'api.github.com/repos/laravel/laravel' => Http::response([
                'description' => 'Updated description',
                'stargazers_count' => 200,
                'forks_count' => 30,
                'open_issues_count' => 3,
                'language' => 'PHP',
                'default_branch' => 'main',
                'html_url' => 'https://github.com/laravel/laravel',
                'visibility' => 'public',
            ], 200),
            'api.github.com/repos/laravel/laravel/branches*' => Http::response([['name' => 'main']], 200),
            'api.github.com/repos/laravel/laravel/commits*' => Http::response([], 200),
        ]);

        $link = $this->makeLink();

        (new SyncGitHubRepositoryJob($link))->handle(app(GitHubClientInterface::class), app(GithubLinkRepositoryInterface::class));

        $link->refresh();
        $this->assertSame(SyncStatus::Synced, $link->sync_status);
        $this->assertSame(200, $link->metadata['stars']);
        $this->assertSame('Updated description', $link->metadata['description']);
        $this->assertNotNull($link->last_synced_at);
    }

    public function test_a_failed_run_marks_the_link_failed_without_throwing(): void
    {
        Http::fake(['api.github.com/repos/laravel/laravel' => Http::response([], 404)]);

        $link = $this->makeLink();

        (new SyncGitHubRepositoryJob($link))->handle(app(GitHubClientInterface::class), app(GithubLinkRepositoryInterface::class));

        $link->refresh();
        $this->assertSame(SyncStatus::Failed, $link->sync_status);
        $this->assertNotNull($link->metadata['error']);
    }
}
