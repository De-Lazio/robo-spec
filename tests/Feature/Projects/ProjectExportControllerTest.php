<?php

namespace Tests\Feature\Projects;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Component;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\ProjectMember;
use App\Models\RequirementsDocument;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProjectExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_export_the_project_as_pdf(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        RequirementsDocument::factory()->published()->create(['project_id' => $project->getKey()]);
        $component = Component::factory()->create();
        ProjectComponent::query()->create([
            'project_id' => $project->getKey(),
            'component_id' => $component->getKey(),
            'quantity' => 2,
            'added_by' => $owner->getKey(),
        ]);
        Task::factory()->create(['project_id' => $project->getKey()]);

        $response = $this->actingAs($owner)->post(route('projects.export', $project), [
            'sections' => [
                'generalInfo' => '1',
                'requirements' => '1',
                'team' => '1',
                'technicalChoices' => '1',
                'algorithmDiagrams' => '1',
                'tasks' => '1',
                'resources' => '1',
            ],
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('export.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_an_outsider_cannot_export_the_project(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->post(route('projects.export', $project))->assertForbidden();
    }

    public function test_exporting_logs_an_activity_and_notifies_other_members(): void
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

        $this->actingAs($owner)->post(route('projects.export', $project))->assertOk();

        $this->assertDatabaseHas('project_activities', [
            'project_id' => $project->getKey(),
            'event' => 'export.generated',
        ]);
        Notification::assertSentTo($manager, ProjectActivityNotification::class);
    }
}
