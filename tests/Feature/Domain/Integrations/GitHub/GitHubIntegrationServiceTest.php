<?php

namespace Tests\Feature\Domain\Integrations\GitHub;

use App\Domain\Integrations\GitHub\Enums\SyncStatus;
use App\Domain\Integrations\GitHub\Services\GitHubIntegrationService;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Jobs\SyncGitHubRepositoryJob;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GitHubIntegrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function fakeRepositoryResponses(string $owner = 'laravel', string $repo = 'laravel'): void
    {
        Http::fake([
            "api.github.com/repos/{$owner}/{$repo}" => Http::response([
                'description' => 'A PHP framework',
                'stargazers_count' => 100,
                'forks_count' => 20,
                'open_issues_count' => 5,
                'language' => 'PHP',
                'default_branch' => 'main',
                'html_url' => "https://github.com/{$owner}/{$repo}",
                'visibility' => 'public',
                'pushed_at' => '2026-01-01T00:00:00Z',
            ], 200),
            "api.github.com/repos/{$owner}/{$repo}/branches*" => Http::response([
                ['name' => 'main'],
                ['name' => 'develop'],
            ], 200),
            "api.github.com/repos/{$owner}/{$repo}/commits*" => Http::response([
                ['sha' => 'abcdef1234567890', 'commit' => ['message' => "Fix bug\n\nMore details", 'author' => ['name' => 'John Doe', 'date' => '2026-01-01T00:00:00Z']]],
            ], 200),
        ]);
    }

    public function test_link_notifies_other_members(): void
    {
        Notification::fake();
        $this->fakeRepositoryResponses();

        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $contributor->getKey(),
            'role' => ProjectMemberRole::Contributor,
            'joined_at' => now(),
        ]);

        app(GitHubIntegrationService::class)->link($project, $owner, 'https://github.com/laravel/laravel');

        Notification::assertSentTo($contributor, ProjectActivityNotification::class);
        Notification::assertNotSentTo($owner, ProjectActivityNotification::class);
    }

    public function test_link_fetches_metadata_and_stores_the_link(): void
    {
        $this->fakeRepositoryResponses();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(GitHubIntegrationService::class);

        $link = $service->link($project, $owner, 'https://github.com/laravel/laravel');

        $this->assertSame('laravel', $link->owner);
        $this->assertSame('laravel', $link->repository);
        $this->assertSame(SyncStatus::Synced, $link->sync_status);
        $this->assertSame('main', $link->default_branch);
        $this->assertSame(100, $link->metadata['stars']);
        $this->assertSame(['main', 'develop'], $link->metadata['branches']);
        $this->assertSame('Fix bug', $link->metadata['commits'][0]['message']);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'github.linked']);
    }

    public function test_link_rejects_a_non_github_url(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(GitHubIntegrationService::class);

        $this->expectException(ValidationException::class);

        $service->link($project, $owner, 'https://gitlab.com/laravel/laravel');
    }

    public function test_link_rejects_a_second_link_for_the_same_project(): void
    {
        $this->fakeRepositoryResponses();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(GitHubIntegrationService::class);

        $service->link($project, $owner, 'https://github.com/laravel/laravel');

        $this->expectException(ValidationException::class);

        $service->link($project, $owner, 'https://github.com/laravel/laravel');
    }

    public function test_link_surfaces_a_clear_error_for_a_missing_repository(): void
    {
        Http::fake(['api.github.com/repos/ghost/ghost' => Http::response([], 404)]);

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(GitHubIntegrationService::class);

        $this->expectException(ValidationException::class);

        $service->link($project, $owner, 'https://github.com/ghost/ghost');
    }

    public function test_sync_dispatches_the_job_and_marks_pending(): void
    {
        Queue::fake();
        $this->fakeRepositoryResponses();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(GitHubIntegrationService::class);
        $link = $service->link($project, $owner, 'https://github.com/laravel/laravel');

        $service->sync($link, $owner);

        $this->assertSame(SyncStatus::Pending, $link->fresh()->sync_status);
        Queue::assertPushed(SyncGitHubRepositoryJob::class, fn (SyncGitHubRepositoryJob $job) => $job->repository->is($link));
    }

    public function test_unlink_removes_the_row(): void
    {
        $this->fakeRepositoryResponses();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(GitHubIntegrationService::class);
        $link = $service->link($project, $owner, 'https://github.com/laravel/laravel');

        $service->unlink($link, $owner);

        $this->assertDatabaseMissing('github_repositories', ['id' => $link->getKey()]);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'github.unlinked']);
    }
}
