<?php

namespace Tests\Feature\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Enums\ResourceKind;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_view_the_resources_index(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->get(route('projects.resources.index', $project))
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Resources'));
    }

    public function test_an_outsider_cannot_view_or_upload_resources(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('projects.resources.index', $project))->assertForbidden();
        $this->actingAs($outsider)->post(route('projects.resources.store', $project), [
            'file' => UploadedFile::fake()->image('plan.png'),
            'category' => 'mechanical',
        ])->assertForbidden();
    }

    public function test_a_contributor_can_upload_a_resource(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $contributor, ProjectMemberRole::Contributor);

        $this->actingAs($contributor)->post(route('projects.resources.store', $project), [
            'file' => UploadedFile::fake()->image('plan.png'),
            'category' => 'mechanical',
            'description' => 'Plan du châssis',
        ])->assertRedirect();

        $this->assertDatabaseHas('resources', [
            'project_id' => $project->getKey(),
            'uploaded_by' => $contributor->getKey(),
            'category' => 'mechanical',
        ]);
    }

    public function test_a_disallowed_extension_is_rejected(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)->post(route('projects.resources.store', $project), [
            'file' => UploadedFile::fake()->create('malware.exe', 10),
            'category' => 'other',
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('resources', 0);
    }

    public function test_a_member_can_download_a_resource(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        Storage::disk('local')->put('projects/fake/resources/fake/plan.pdf', 'contenu');
        $resource = Resource::factory()->create([
            'project_id' => $project->getKey(),
            'uploaded_by' => $owner->getKey(),
            'category' => ResourceCategory::Other,
            'kind' => ResourceKind::Document,
            'path' => 'projects/fake/resources/fake/plan.pdf',
        ]);

        $this->actingAs($owner)
            ->get(route('projects.resources.download', [$project, $resource]))
            ->assertOk();
    }

    public function test_a_resource_from_another_project_is_not_reachable_through_the_scoped_route(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $otherProject = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $resource = Resource::factory()->create([
            'project_id' => $otherProject->getKey(),
            'uploaded_by' => $owner->getKey(),
        ]);

        $this->actingAs($owner)
            ->get(route('projects.resources.download', [$project, $resource]))
            ->assertNotFound();
    }

    public function test_an_stl_resource_is_previewable_but_a_step_resource_is_not(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        Storage::disk('local')->put('projects/fake/resources/stl/model.stl', 'solid test endsolid test');
        $stlResource = Resource::factory()->create([
            'project_id' => $project->getKey(),
            'uploaded_by' => $owner->getKey(),
            'category' => ResourceCategory::Mechanical,
            'kind' => ResourceKind::Cad,
            'original_name' => 'model.stl',
            'path' => 'projects/fake/resources/stl/model.stl',
        ]);

        Storage::disk('local')->put('projects/fake/resources/step/model.step', 'not previewable');
        $stepResource = Resource::factory()->create([
            'project_id' => $project->getKey(),
            'uploaded_by' => $owner->getKey(),
            'category' => ResourceCategory::Mechanical,
            'kind' => ResourceKind::Cad,
            'original_name' => 'model.step',
            'path' => 'projects/fake/resources/step/model.step',
        ]);

        $this->actingAs($owner)
            ->get(route('projects.resources.preview', [$project, $stlResource]))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('projects.resources.preview', [$project, $stepResource]))
            ->assertNotFound();

        $response = $this->actingAs($owner)
            ->get(route('projects.resources.index', $project), [
                'X-Inertia' => 'true',
                'X-Inertia-Version' => Inertia::getVersion(),
            ]);
        $response->assertOk();

        $resources = collect($response->json('props.resources'));
        $stl = $resources->firstWhere('original_name', 'model.stl');
        $step = $resources->firstWhere('original_name', 'model.step');

        $this->assertSame('model', $stl['preview_kind']);
        $this->assertNotNull($stl['preview_url']);
        $this->assertNull($step['preview_kind']);
        $this->assertNull($step['preview_url']);
    }

    public function test_a_folder_can_be_set_at_upload_and_appears_in_the_index_payload(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)->post(route('projects.resources.store', $project), [
            'file' => UploadedFile::fake()->image('plan.png'),
            'category' => 'mechanical',
            'folder' => 'Châssis',
        ])->assertRedirect();

        $this->assertDatabaseHas('resources', [
            'project_id' => $project->getKey(),
            'folder' => 'Châssis',
        ]);
    }

    public function test_uploader_can_move_own_resource_but_unrelated_contributor_cannot(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $uploader = User::factory()->create();
        $otherContributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $uploader, ProjectMemberRole::Contributor);
        $this->createMember($project, $otherContributor, ProjectMemberRole::Contributor);

        $resource = Resource::factory()->create([
            'project_id' => $project->getKey(),
            'uploaded_by' => $uploader->getKey(),
        ]);

        $this->actingAs($otherContributor)
            ->patch(route('projects.resources.update', [$project, $resource]), ['folder' => 'Nouveau dossier'])
            ->assertForbidden();

        $this->actingAs($uploader)
            ->patch(route('projects.resources.update', [$project, $resource]), ['folder' => 'Nouveau dossier'])
            ->assertRedirect();

        $this->assertDatabaseHas('resources', ['id' => $resource->getKey(), 'folder' => 'Nouveau dossier']);
    }

    public function test_uploader_can_delete_own_resource_but_unrelated_contributor_cannot(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $uploader = User::factory()->create();
        $otherContributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $uploader, ProjectMemberRole::Contributor);
        $this->createMember($project, $otherContributor, ProjectMemberRole::Contributor);

        $resource = Resource::factory()->create([
            'project_id' => $project->getKey(),
            'uploaded_by' => $uploader->getKey(),
        ]);

        $this->actingAs($otherContributor)
            ->delete(route('projects.resources.destroy', [$project, $resource]))
            ->assertForbidden();

        $this->actingAs($uploader)
            ->delete(route('projects.resources.destroy', [$project, $resource]))
            ->assertRedirect();

        $this->assertSoftDeleted('resources', ['id' => $resource->getKey()]);
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
