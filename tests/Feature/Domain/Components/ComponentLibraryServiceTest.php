<?php

namespace Tests\Feature\Domain\Components;

use App\Domain\Components\DTOs\ComponentData;
use App\Domain\Components\DTOs\CreateComponentCategoryData;
use App\Domain\Components\Enums\ComponentType;
use App\Domain\Components\Services\ComponentLibraryService;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Project;
use App\Models\ProjectComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Throwable;

class ComponentLibraryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_update_a_category(): void
    {
        $service = app(ComponentLibraryService::class);

        $category = $service->createCategory(new CreateComponentCategoryData(type: ComponentType::Sensor, name: 'Distance'));
        $this->assertSame('Sensor', $category->type->name);
        $this->assertSame('distance', $category->slug);

        $updated = $service->updateCategory($category, 'Proximité');
        $this->assertSame('Proximité', $updated->name);
        $this->assertSame('proximite', $updated->slug);
    }

    public function test_delete_category_rejects_when_components_are_attached(): void
    {
        $service = app(ComponentLibraryService::class);
        $owner = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        Component::factory()->create(['component_category_id' => $category->getKey(), 'created_by' => $owner->getKey()]);

        $this->expectException(ValidationException::class);
        $service->deleteCategory($category);
    }

    public function test_delete_category_succeeds_when_empty(): void
    {
        $service = app(ComponentLibraryService::class);
        $category = ComponentCategory::factory()->create();

        $service->deleteCategory($category);

        $this->assertDatabaseMissing('component_categories', ['id' => $category->getKey()]);
    }

    public function test_create_component_with_a_datasheet(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        $service = app(ComponentLibraryService::class);

        $component = $service->createComponent($owner, new ComponentData(
            categoryId: $category->getKey(),
            name: 'ESP32',
            manufacturer: 'Espressif',
            reference: 'WROOM-32',
            description: 'Microcontrôleur WiFi/BLE',
            specs: ['tension' => '3.3V'],
            datasheet: UploadedFile::fake()->create('datasheet.pdf', 100, 'application/pdf'),
            priceCents: 500,
            currency: 'EUR',
            supplierUrl: 'https://example.com',
        ));

        $this->assertSame('ESP32', $component->name);
        Storage::disk('local')->assertExists($component->datasheet_path);
        $this->assertDatabaseHas('components', ['id' => $component->getKey(), 'created_by' => $owner->getKey()]);
    }

    public function test_create_component_compensates_when_the_db_insert_fails(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $service = app(ComponentLibraryService::class);

        try {
            $service->createComponent($owner, new ComponentData(
                categoryId: 999_999, // does not exist -> FK violation on insert
                name: 'Ghost Component',
                manufacturer: null,
                reference: null,
                description: null,
                specs: [],
                datasheet: UploadedFile::fake()->create('datasheet.pdf', 100, 'application/pdf'),
                priceCents: null,
                currency: null,
                supplierUrl: null,
            ));
            $this->fail('Expected the FK violation to throw.');
        } catch (Throwable) {
            // expected
        }

        Storage::disk('local')->assertDirectoryEmpty('components');
        $this->assertDatabaseCount('components', 0);
    }

    public function test_create_component_rejects_a_non_pdf_datasheet(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        $service = app(ComponentLibraryService::class);

        $this->expectException(ValidationException::class);
        $service->createComponent($owner, new ComponentData(
            categoryId: $category->getKey(),
            name: 'Bad file',
            manufacturer: null,
            reference: null,
            description: null,
            specs: [],
            datasheet: UploadedFile::fake()->create('malware.exe', 10),
            priceCents: null,
            currency: null,
            supplierUrl: null,
        ));
    }

    public function test_deactivate_and_reactivate_a_component(): void
    {
        $owner = User::factory()->create();
        $component = Component::factory()->create(['created_by' => $owner->getKey()]);
        $service = app(ComponentLibraryService::class);

        $deactivated = $service->deactivateComponent($component);
        $this->assertFalse($deactivated->is_active);

        $reactivated = $service->reactivateComponent($component);
        $this->assertTrue($reactivated->is_active);
    }

    public function test_create_local_component_stamps_the_owner_project_id(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $category = ComponentCategory::factory()->create();
        $service = app(ComponentLibraryService::class);

        $component = $service->createLocalComponent($project, $owner, new ComponentData(
            categoryId: $category->getKey(),
            name: 'Capteur maison',
            manufacturer: null,
            reference: null,
            description: null,
            specs: [],
            datasheet: null,
            priceCents: null,
            currency: null,
            supplierUrl: null,
        ));

        $this->assertSame($project->getKey(), $component->owner_project_id);
        $this->assertDatabaseHas('components', ['id' => $component->getKey(), 'owner_project_id' => $project->getKey()]);
    }

    public function test_create_component_with_an_external_image_url(): void
    {
        $owner = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        $service = app(ComponentLibraryService::class);

        $component = $service->createComponent($owner, new ComponentData(
            categoryId: $category->getKey(),
            name: 'ESP32',
            manufacturer: null,
            reference: null,
            description: null,
            specs: [],
            datasheet: null,
            priceCents: null,
            currency: null,
            supplierUrl: null,
            imageUrl: 'https://commons.wikimedia.org/wiki/Special:FilePath/Example.jpg',
        ));

        $this->assertSame('https://commons.wikimedia.org/wiki/Special:FilePath/Example.jpg', $component->image_url);
        $this->assertNull($component->image_path);
    }

    public function test_create_component_with_an_uploaded_image(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        $service = app(ComponentLibraryService::class);

        $component = $service->createComponent($owner, new ComponentData(
            categoryId: $category->getKey(),
            name: 'ESP32',
            manufacturer: null,
            reference: null,
            description: null,
            specs: [],
            datasheet: null,
            priceCents: null,
            currency: null,
            supplierUrl: null,
            image: UploadedFile::fake()->image('photo.jpg'),
        ));

        Storage::disk('local')->assertExists($component->image_path);
        $this->assertSame('local', $component->image_disk);
    }

    public function test_create_component_rejects_a_non_image_file_for_image(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        $service = app(ComponentLibraryService::class);

        $this->expectException(ValidationException::class);
        $service->createComponent($owner, new ComponentData(
            categoryId: $category->getKey(),
            name: 'Bad image',
            manufacturer: null,
            reference: null,
            description: null,
            specs: [],
            datasheet: null,
            priceCents: null,
            currency: null,
            supplierUrl: null,
            image: UploadedFile::fake()->create('malware.exe', 10),
        ));
    }

    public function test_updating_a_component_replaces_the_previous_image_file(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        $service = app(ComponentLibraryService::class);

        $component = $service->createComponent($owner, new ComponentData(
            categoryId: $category->getKey(),
            name: 'ESP32',
            manufacturer: null,
            reference: null,
            description: null,
            specs: [],
            datasheet: null,
            priceCents: null,
            currency: null,
            supplierUrl: null,
            image: UploadedFile::fake()->image('photo-v1.jpg'),
        ));
        $originalPath = $component->image_path;

        $updated = $service->updateComponent($component, new ComponentData(
            categoryId: $category->getKey(),
            name: 'ESP32',
            manufacturer: null,
            reference: null,
            description: null,
            specs: [],
            datasheet: null,
            priceCents: null,
            currency: null,
            supplierUrl: null,
            image: UploadedFile::fake()->image('photo-v2.jpg'),
        ));

        Storage::disk('local')->assertMissing($originalPath);
        Storage::disk('local')->assertExists($updated->image_path);
    }

    public function test_delete_component_removes_the_stored_image_file(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        $service = app(ComponentLibraryService::class);

        $component = $service->createComponent($owner, new ComponentData(
            categoryId: $category->getKey(),
            name: 'ESP32',
            manufacturer: null,
            reference: null,
            description: null,
            specs: [],
            datasheet: null,
            priceCents: null,
            currency: null,
            supplierUrl: null,
            image: UploadedFile::fake()->image('photo.jpg'),
        ));
        $imagePath = $component->image_path;

        $service->deleteComponent($component);

        Storage::disk('local')->assertMissing($imagePath);
    }

    public function test_delete_component_rejects_when_still_used_in_a_technical_choice(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $component = Component::factory()->create(['created_by' => $owner->getKey()]);
        ProjectComponent::query()->create([
            'project_id' => $project->getKey(),
            'component_id' => $component->getKey(),
            'quantity' => 1,
            'added_by' => $owner->getKey(),
        ]);
        $service = app(ComponentLibraryService::class);

        $this->expectException(ValidationException::class);
        $service->deleteComponent($component);
    }
}
