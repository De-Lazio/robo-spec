<?php

namespace Tests\Feature\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Jobs\SyncGitHubRepositoryJob;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GitHubControllerTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSuccessfulLink(): void
    {
        Http::fake([
            'api.github.com/repos/laravel/laravel' => Http::response([
                'description' => 'A PHP framework',
                'stargazers_count' => 100,
                'forks_count' => 20,
                'open_issues_count' => 5,
                'language' => 'PHP',
                'default_branch' => 'main',
                'html_url' => 'https://github.com/laravel/laravel',
                'visibility' => 'public',
            ], 200),
            'api.github.com/repos/laravel/laravel/branches*' => Http::response([['name' => 'main']], 200),
            'api.github.com/repos/laravel/laravel/commits*' => Http::response([], 200),
        ]);
    }

    public function test_an_outsider_cannot_view_or_manage_the_github_link(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('projects.github.show', $project))->assertForbidden();
        $this->actingAs($outsider)->post(route('projects.github.store', $project), ['url' => 'https://github.com/laravel/laravel'])->assertForbidden();
    }

    public function test_a_contributor_can_view_but_not_link_or_unlink(): void
    {
        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $contributor, ProjectMemberRole::Contributor);

        $this->actingAs($contributor)
            ->get(route('projects.github.show', $project))
            ->assertInertia(fn (Assert $page) => $page->component('Projects/GitHub')->where('canManage', false));

        $this->actingAs($contributor)->post(route('projects.github.store', $project), ['url' => 'https://github.com/laravel/laravel'])->assertForbidden();
        $this->actingAs($contributor)->delete(route('projects.github.destroy', $project))->assertForbidden();
    }

    public function test_a_manager_can_link_a_repository(): void
    {
        $this->fakeSuccessfulLink();

        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $manager, ProjectMemberRole::Manager);

        $this->actingAs($manager)
            ->post(route('projects.github.store', $project), ['url' => 'https://github.com/laravel/laravel'])
            ->assertRedirect();

        $this->assertDatabaseHas('github_repositories', ['project_id' => $project->getKey(), 'owner' => 'laravel', 'repository' => 'laravel']);

        $this->actingAs($manager)
            ->get(route('projects.github.show', $project))
            ->assertInertia(fn (Assert $page) => $page->component('Projects/GitHub')->where('repository.owner', 'laravel'));
    }

    public function test_an_invalid_url_is_rejected_with_a_clear_error(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->post(route('projects.github.store', $project), ['url' => 'not-a-github-url'])
            ->assertSessionHasErrors('url');

        $this->assertDatabaseCount('github_repositories', 0);
    }

    public function test_an_owner_can_sync_and_unlink(): void
    {
        $this->fakeSuccessfulLink();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)->post(route('projects.github.store', $project), ['url' => 'https://github.com/laravel/laravel'])->assertRedirect();

        Queue::fake();
        $this->actingAs($owner)->post(route('projects.github.sync', $project))->assertRedirect();
        Queue::assertPushed(SyncGitHubRepositoryJob::class);

        $this->actingAs($owner)->delete(route('projects.github.destroy', $project))->assertRedirect();
        $this->assertDatabaseCount('github_repositories', 0);
    }

    private function createMember(Project $project, User $user, ProjectMemberRole $role): ProjectMember
    {
        return ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $user->getKey(),
            'role' => $role,
            'joined_at' => now(),
        ]);
    }
}
