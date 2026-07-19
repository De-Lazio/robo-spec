<?php

namespace Tests\Feature\Domain\Requirements;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Projects\Enums\RobotType;
use App\Domain\Requirements\Enums\RequirementsStatus;
use App\Domain\Requirements\Services\RequirementsService;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RequirementsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_or_create_draft_creates_version_one_prefilled_from_the_project(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey(), 'name' => 'Robot AGV', 'robot_type' => RobotType::Mobile]);
        $service = app(RequirementsService::class);

        $draft = $service->getOrCreateDraft($project, $owner);

        $this->assertSame(1, $draft->version);
        $this->assertSame(RequirementsStatus::Draft, $draft->status);
        $this->assertSame('Robot AGV', $draft->data['step1']['projectName']);
        $this->assertSame(RobotType::Mobile->value, $draft->data['step1']['projectType']);

        $again = $service->getOrCreateDraft($project, $owner);
        $this->assertSame($draft->getKey(), $again->getKey());
        $this->assertDatabaseCount('requirements_documents', 1);
    }

    public function test_save_step_persists_data_and_updates_current_step(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(RequirementsService::class);
        $draft = $service->getOrCreateDraft($project, $owner);

        $updated = $service->saveStep($draft, 3, ['missions' => ['Transporter des colis']], $owner);

        $this->assertSame(['missions' => ['Transporter des colis']], $updated->data['step3']);
        $this->assertSame(3, $updated->current_step);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'requirements.step_saved']);
    }

    public function test_save_draft_persists_an_incomplete_document_without_validation(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(RequirementsService::class);
        $draft = $service->getOrCreateDraft($project, $owner);

        $incomplete = $draft->data;
        $incomplete['step2'] = ['context' => '', 'problem' => '', 'whyRobot' => ''];

        $updated = $service->saveDraft($draft, $incomplete, $owner);

        $this->assertSame('', $updated->fresh()->data['step2']['context']);
    }

    public function test_publish_rejects_an_incomplete_document_and_reports_the_failing_steps(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(RequirementsService::class);
        $draft = $service->getOrCreateDraft($project, $owner);

        try {
            $service->publish($draft, $owner);
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            $keys = array_keys($exception->errors());
            $this->assertNotEmpty($keys);
            $this->assertStringStartsWith('step1.', $keys[0]);
        }

        $this->assertSame(RequirementsStatus::Draft, $draft->fresh()->status);
    }

    public function test_publish_locks_a_complete_document_and_editing_again_forks_a_new_version(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(RequirementsService::class);
        $draft = $service->getOrCreateDraft($project, $owner);

        foreach ($this->completeStepData() as $step => $data) {
            $service->saveStep($draft, $step, $data, $owner);
        }

        $published = $service->publish($draft->fresh(), $owner);

        $this->assertSame(RequirementsStatus::Published, $published->status);
        $this->assertNotNull($published->published_at);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'requirements.published']);

        $newDraft = $service->getOrCreateDraft($project, $owner);

        $this->assertSame(2, $newDraft->version);
        $this->assertSame(RequirementsStatus::Draft, $newDraft->status);
        $this->assertSame(1, $newDraft->current_step);
        $this->assertSame($published->data, $newDraft->data);
        $this->assertSame(RequirementsStatus::Published, $published->fresh()->status);
    }

    public function test_publishing_notifies_other_members_by_mail_and_in_app(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $manager->getKey(),
            'role' => ProjectMemberRole::Manager,
            'joined_at' => now(),
        ]);
        $service = app(RequirementsService::class);
        $draft = $service->getOrCreateDraft($project, $owner);

        foreach ($this->completeStepData() as $step => $data) {
            $service->saveStep($draft, $step, $data, $owner);
        }

        $service->publish($draft->fresh(), $owner);

        Notification::assertSentTo($manager, ProjectActivityNotification::class, function (ProjectActivityNotification $notification, array $channels): bool {
            return $notification->activity->event === 'requirements.published' && in_array('mail', $channels, true);
        });
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
