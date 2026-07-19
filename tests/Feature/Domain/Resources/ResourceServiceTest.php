<?php

namespace Tests\Feature\Domain\Resources;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Domain\Resources\DTOs\UploadResourceData;
use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Services\ResourceService;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResourceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_notifies_other_members(): void
    {
        Storage::fake('local');
        Notification::fake();

        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        ProjectMember::query()->create([
            'project_id' => $project->getKey(),
            'user_id' => $contributor->getKey(),
            'role' => ProjectMemberRole::Contributor,
            'joined_at' => now(),
        ]);

        app(ResourceService::class)->upload($project, $owner, new UploadResourceData(
            file: UploadedFile::fake()->image('plan.png'),
            category: ResourceCategory::Mechanical,
            description: null,
        ));

        Notification::assertSentTo($contributor, ProjectActivityNotification::class);
        Notification::assertNotSentTo($owner, ProjectActivityNotification::class);
    }

    public function test_upload_stores_the_file_creates_the_row_and_logs_an_activity(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(ResourceService::class);

        $file = UploadedFile::fake()->image('plan.png', 200, 200);

        $resource = $service->upload($project, $owner, new UploadResourceData(
            file: $file,
            category: ResourceCategory::Mechanical,
            description: 'Plan mécanique',
        ));

        $this->assertSame('image', $resource->kind->value);
        $this->assertSame('mechanical', $resource->category->value);
        Storage::disk('local')->assertExists($resource->path);
        $this->assertDatabaseHas('resources', ['id' => $resource->getKey(), 'project_id' => $project->getKey()]);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'resource.uploaded']);
    }

    public function test_upload_rejects_an_extension_outside_the_whitelist(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(ResourceService::class);

        $file = UploadedFile::fake()->create('malware.exe', 10);

        $this->expectException(ValidationException::class);

        $service->upload($project, $owner, new UploadResourceData(
            file: $file,
            category: ResourceCategory::Other,
            description: null,
        ));
    }

    public function test_upload_rejects_content_that_does_not_match_a_strict_kinds_extension(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(ResourceService::class);

        $file = UploadedFile::fake()->create('fake.png', 10)->mimeType('text/plain');

        $this->expectException(ValidationException::class);

        $service->upload($project, $owner, new UploadResourceData(
            file: $file,
            category: ResourceCategory::Other,
            description: null,
        ));
    }

    public function test_upload_persists_a_trimmed_folder_and_treats_a_blank_folder_as_none(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(ResourceService::class);

        $resource = $service->upload($project, $owner, new UploadResourceData(
            file: UploadedFile::fake()->image('plan.png'),
            category: ResourceCategory::Mechanical,
            description: null,
            folder: '  Châssis  ',
        ));

        $this->assertSame('Châssis', $resource->folder);

        $blank = $service->upload($project, $owner, new UploadResourceData(
            file: UploadedFile::fake()->image('plan2.png'),
            category: ResourceCategory::Mechanical,
            description: null,
            folder: '   ',
        ));

        $this->assertNull($blank->folder);
    }

    public function test_move_to_folder_updates_the_folder_and_logs_an_activity(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(ResourceService::class);

        $resource = $service->upload($project, $owner, new UploadResourceData(
            file: UploadedFile::fake()->image('plan.png'),
            category: ResourceCategory::Mechanical,
            description: null,
        ));

        $moved = $service->moveToFolder($resource, $owner, 'Bras robotisé');

        $this->assertSame('Bras robotisé', $moved->folder);
        $this->assertDatabaseHas('resources', ['id' => $resource->getKey(), 'folder' => 'Bras robotisé']);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'resource.updated']);
    }

    public function test_delete_soft_deletes_the_row_and_keeps_the_file(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $service = app(ResourceService::class);

        $resource = $service->upload($project, $owner, new UploadResourceData(
            file: UploadedFile::fake()->image('plan.png'),
            category: ResourceCategory::Electronics,
            description: null,
        ));

        $service->delete($resource, $owner);

        $this->assertSoftDeleted('resources', ['id' => $resource->getKey()]);
        Storage::disk('local')->assertExists($resource->path);
        $this->assertDatabaseHas('project_activities', ['project_id' => $project->getKey(), 'event' => 'resource.deleted']);
    }
}
