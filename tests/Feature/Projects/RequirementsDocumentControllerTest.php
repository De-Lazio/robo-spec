<?php

namespace Tests\Feature\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RequirementsDocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_outsider_cannot_access_any_requirements_action(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('projects.requirements.show', $project))->assertForbidden();
        $this->actingAs($outsider)->get(route('projects.requirements.edit', $project))->assertForbidden();
        $this->actingAs($outsider)->put(route('projects.requirements.steps.save', [$project, 1]), [])->assertForbidden();
        $this->actingAs($outsider)->put(route('projects.requirements.draft.save', $project), ['data' => []])->assertForbidden();
        $this->actingAs($outsider)->post(route('projects.requirements.publish', $project))->assertForbidden();
    }

    public function test_a_viewer_can_view_but_not_edit_or_publish(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $viewer = User::factory()->create();
        ProjectMember::query()->create(['project_id' => $project->getKey(), 'user_id' => $viewer->getKey(), 'role' => ProjectMemberRole::Viewer, 'joined_at' => now()]);

        $this->actingAs($viewer)->get(route('projects.requirements.show', $project))->assertOk();
        $this->actingAs($viewer)->get(route('projects.requirements.edit', $project))->assertForbidden();
        $this->actingAs($viewer)->put(route('projects.requirements.draft.save', $project), ['data' => []])->assertForbidden();
        $this->actingAs($viewer)->post(route('projects.requirements.publish', $project))->assertForbidden();
    }

    public function test_show_never_creates_a_document_for_a_project_with_none_yet(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->get(route('projects.requirements.show', $project))
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Requirements/Show')->where('requirementsDocument', null));

        $this->assertDatabaseCount('requirements_documents', 0);
    }

    public function test_edit_creates_a_draft_prefilled_from_the_project(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey(), 'name' => 'Robot Trieur']);

        $this->actingAs($owner)
            ->get(route('projects.requirements.edit', $project))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Requirements/Edit')
                ->where('requirementsDocument.data.step1.projectName', 'Robot Trieur'));

        $this->assertDatabaseCount('requirements_documents', 1);
    }

    public function test_save_step_rejects_invalid_data_and_accepts_valid_data(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->put(route('projects.requirements.steps.save', [$project, 1]), ['projectName' => '', 'team' => '', 'date' => ''])
            ->assertSessionHasErrors(['projectName', 'team', 'date']);

        $this->actingAs($owner)
            ->put(route('projects.requirements.steps.save', [$project, 1]), [
                'projectName' => 'Robot Trieur', 'team' => 'Groupe A', 'date' => '2026-01-01', 'projectType' => '', 'supervisor' => '',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('requirements_documents', ['project_id' => $project->getKey(), 'current_step' => 1]);
    }

    public function test_a_contributor_can_complete_all_steps_and_publish(): void
    {
        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        ProjectMember::query()->create(['project_id' => $project->getKey(), 'user_id' => $contributor->getKey(), 'role' => ProjectMemberRole::Contributor, 'joined_at' => now()]);

        $this->actingAs($contributor)->get(route('projects.requirements.edit', $project))->assertOk();

        foreach ($this->completeStepData() as $step => $data) {
            $this->actingAs($contributor)
                ->put(route('projects.requirements.steps.save', [$project, $step]), $data)
                ->assertRedirect();
        }

        $this->actingAs($contributor)
            ->post(route('projects.requirements.publish', $project))
            ->assertRedirect(route('projects.requirements.show', $project));

        $this->assertDatabaseHas('requirements_documents', ['project_id' => $project->getKey(), 'status' => 'published']);

        $this->actingAs($contributor)
            ->get(route('projects.requirements.show', $project))
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Requirements/Show')->has('requirementsDocument'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function completeStepData(): array
    {
        return [
            1 => ['projectName' => 'Robot AGV', 'team' => 'Groupe A', 'date' => '2026-01-01', 'projectType' => 'Robot Mobile Autonome', 'supervisor' => 'Prof. X'],
            2 => ['context' => 'Entrepôt logistique.', 'problem' => 'Transport manuel lent.', 'whyRobot' => 'Automatiser le transport.'],
            3 => ['missions' => ['Transporter des colis de 0 à 25 kg']],
            4 => ['users' => ['Techniciens'], 'otherUsers' => ''],
            5 => ['functions' => [['id' => 'F1', 'name' => 'Détecter les obstacles'], ['id' => 'F2', 'name' => 'Se déplacer']]],
            6 => ['maxSize' => '', 'maxWeight' => '', 'minAutonomy' => '', 'minSpeed' => '', 'maxBudget' => '', 'estimatedCost' => '', 'safetyConstraints' => ["Arrêt d'urgence physique"], 'temperature' => '', 'usageConditions' => ''],
            7 => ['criteria' => [['name' => 'Autonomie', 'value' => '4 heures']]],
            8 => ['expectedResult' => 'Transport autonome fiable.', 'successCriteria' => '5 trajets réussis.', 'testMethod' => ''],
        ];
    }
}
