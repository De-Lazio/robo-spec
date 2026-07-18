<?php

namespace Database\Seeders;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Resources\Enums\ResourceCategory;
use App\Models\GithubRepository;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectMember;
use App\Models\RequirementsDocument;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with a demo dataset.
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $teammates = User::factory(2)->create();

        $organization = Organization::factory()->create(['owner_id' => $owner->getKey()]);

        OrganizationMember::create([
            'organization_id' => $organization->getKey(),
            'user_id' => $owner->getKey(),
            'role' => OrganizationRole::Owner,
            'joined_at' => now(),
        ]);

        foreach ($teammates as $index => $teammate) {
            OrganizationMember::create([
                'organization_id' => $organization->getKey(),
                'user_id' => $teammate->getKey(),
                'role' => $index === 0 ? OrganizationRole::Admin : OrganizationRole::Member,
                'joined_at' => now(),
            ]);
        }

        $progressByStatus = [
            ProjectStatus::InProgress->value => 40,
            ProjectStatus::Testing->value => 75,
            ProjectStatus::Completed->value => 100,
        ];

        foreach ($progressByStatus as $status => $progress) {
            $project = Project::factory()->create([
                'owner_id' => $owner->getKey(),
                'organization_id' => $organization->getKey(),
                'status' => $status,
                'progress' => $progress,
            ]);

            foreach ($teammates as $teammate) {
                ProjectMember::factory()->create([
                    'project_id' => $project->getKey(),
                    'user_id' => $teammate->getKey(),
                ]);
            }

            RequirementsDocument::factory()->published()->create([
                'project_id' => $project->getKey(),
                'created_by' => $owner->getKey(),
                'updated_by' => $owner->getKey(),
            ]);

            foreach ([ResourceCategory::Mechanical, ResourceCategory::Electronics, ResourceCategory::Software] as $category) {
                Resource::factory()->create([
                    'project_id' => $project->getKey(),
                    'uploaded_by' => $owner->getKey(),
                    'category' => $category,
                ]);
            }

            GithubRepository::factory()->create(['project_id' => $project->getKey()]);

            ProjectActivity::create([
                'project_id' => $project->getKey(),
                'actor_id' => $owner->getKey(),
                'event' => 'project.created',
                'subject_type' => Project::class,
                'subject_id' => $project->getKey(),
                'properties' => ['name' => $project->name],
                'created_at' => $project->created_at,
            ]);

            ProjectActivity::create([
                'project_id' => $project->getKey(),
                'actor_id' => $owner->getKey(),
                'event' => 'requirements.published',
                'subject_type' => RequirementsDocument::class,
                'subject_id' => $project->getKey(),
                'properties' => ['version' => 1],
                'created_at' => now(),
            ]);
        }
    }
}
