<?php

namespace Tests\Feature\Projects;

use App\Domain\AlgorithmDiagrams\Enums\DiagramFormalism;
use App\Domain\AlgorithmDiagrams\Services\AlgorithmDiagramService;
use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\AlgorithmDiagram;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlgorithmDiagramControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_outsider_cannot_view_or_manage_diagrams(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('projects.algorithm-diagrams.index', $project))->assertForbidden();
        $this->actingAs($outsider)->post(route('projects.algorithm-diagrams.store', $project), ['name' => 'Séquence', 'formalism' => 'algorigramme'])->assertForbidden();
    }

    public function test_a_contributor_can_create_update_and_delete_a_diagram(): void
    {
        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $this->createMember($project, $contributor, ProjectMemberRole::Contributor);

        $this->actingAs($contributor)
            ->post(route('projects.algorithm-diagrams.store', $project), ['name' => 'Séquence principale', 'formalism' => 'algorigramme'])
            ->assertRedirect();

        $diagram = AlgorithmDiagram::query()->where('project_id', $project->getKey())->firstOrFail();

        $this->actingAs($contributor)->put(route('projects.algorithm-diagrams.update', [$project, $diagram]), [
            'name' => 'Séquence principale (v2)',
            'data' => [
                'nodes' => [
                    ['id' => 'n1', 'type' => 'start', 'position' => ['x' => 0, 'y' => 0], 'data' => ['label' => 'Début']],
                ],
                'edges' => [],
                'variables' => [],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('algorithm_diagrams', ['id' => $diagram->getKey(), 'name' => 'Séquence principale (v2)']);

        $this->actingAs($contributor)
            ->delete(route('projects.algorithm-diagrams.destroy', [$project, $diagram]))
            ->assertRedirect();
        $this->assertDatabaseMissing('algorithm_diagrams', ['id' => $diagram->getKey()]);
    }

    public function test_a_contributor_can_create_a_grafcet_diagram(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->post(route('projects.algorithm-diagrams.store', $project), ['name' => 'GRAFCET principal', 'formalism' => 'grafcet'])
            ->assertRedirect();

        $this->assertDatabaseHas('algorithm_diagrams', ['project_id' => $project->getKey(), 'formalism' => 'grafcet']);
    }

    public function test_an_invalid_formalism_is_rejected(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->post(route('projects.algorithm-diagrams.store', $project), ['name' => 'Séquence', 'formalism' => 'not-a-formalism'])
            ->assertSessionHasErrors('formalism');
    }

    public function test_export_creates_a_resource_in_the_requested_category(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $diagram = app(AlgorithmDiagramService::class)->create($project, $owner, 'Séquence', DiagramFormalism::Algorigramme);

        $this->actingAs($owner)->post(route('projects.algorithm-diagrams.export', [$project, $diagram]), [
            'file' => UploadedFile::fake()->image('diagram.png'),
            'category' => 'software',
        ])->assertRedirect();

        $this->assertDatabaseHas('resources', [
            'project_id' => $project->getKey(),
            'category' => 'software',
        ]);
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
