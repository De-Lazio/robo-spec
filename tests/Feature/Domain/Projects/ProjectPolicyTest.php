<?php

namespace Tests\Feature\Domain\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Enums\ResourceKind;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'project.member'])
            ->get('/_test/projects/{project}', fn (Project $project): array => ['id' => $project->getKey()]);
    }

    public function test_only_project_members_can_view_a_project(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $outsider = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $viewer->getKey(),
            'role' => ProjectMemberRole::Viewer,
            'joined_at' => now(),
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('view', $project));
        $this->assertTrue(Gate::forUser($viewer)->allows('view', $project));
        $this->assertFalse(Gate::forUser($outsider)->allows('view', $project));
    }

    public function test_only_owner_and_manager_can_update_a_project(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        foreach ([[
            'user' => $manager,
            'role' => ProjectMemberRole::Manager,
        ], [
            'user' => $contributor,
            'role' => ProjectMemberRole::Contributor,
        ]] as $member) {
            ProjectMember::query()->create([
                'project_id' => $project->getKey(),
                'user_id' => $member['user']->getKey(),
                'role' => $member['role'],
                'joined_at' => now(),
            ]);
        }

        $this->assertTrue(Gate::forUser($owner)->allows('update', $project));
        $this->assertTrue(Gate::forUser($manager)->allows('update', $project));
        $this->assertFalse(Gate::forUser($contributor)->allows('update', $project));
        $this->assertTrue(Gate::forUser($owner)->allows('archive', $project));
        $this->assertFalse(Gate::forUser($manager)->allows('archive', $project));
    }

    public function test_only_contributing_roles_can_upload_resources(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $viewer->getKey(),
            'role' => ProjectMemberRole::Viewer,
            'joined_at' => now(),
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('uploadResources', $project));
        $this->assertFalse(Gate::forUser($viewer)->allows('uploadResources', $project));
    }

    public function test_a_resource_can_be_deleted_by_managers_or_its_uploader_only(): void
    {
        $owner = User::factory()->create();
        $uploader = User::factory()->create();
        $otherContributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        foreach ([$uploader, $otherContributor] as $user) {
            ProjectMember::query()->create([
                'project_id' => $project->getKey(),
                'user_id' => $user->getKey(),
                'role' => ProjectMemberRole::Contributor,
                'joined_at' => now(),
            ]);
        }

        $resource = Resource::factory()->create([
            'project_id' => $project->getKey(),
            'uploaded_by' => $uploader->getKey(),
            'category' => ResourceCategory::Other,
            'kind' => ResourceKind::Document,
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('deleteResource', [$project, $resource]));
        $this->assertTrue(Gate::forUser($uploader)->allows('deleteResource', [$project, $resource]));
        $this->assertFalse(Gate::forUser($otherContributor)->allows('deleteResource', [$project, $resource]));
    }

    public function test_project_member_middleware_rejects_an_outsider(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->get('/_test/projects/'.$project->getKey())
            ->assertOk()
            ->assertJson(['id' => $project->getKey()]);

        $this->actingAs($outsider)
            ->get('/_test/projects/'.$project->getKey())
            ->assertForbidden();
    }
}
