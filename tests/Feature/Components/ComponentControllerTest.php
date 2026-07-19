<?php

namespace Tests\Feature\Components;

use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ComponentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_browse_and_view(): void
    {
        $regular = User::factory()->create();
        $component = Component::factory()->create(['created_by' => User::factory()->create()->getKey()]);

        $this->actingAs($regular)
            ->get(route('components.index'))
            ->assertInertia(fn (Assert $page) => $page->component('Components/Index')->where('canManage', false));

        $this->actingAs($regular)
            ->get(route('components.show', $component))
            ->assertInertia(fn (Assert $page) => $page->component('Components/Show')->where('component.name', $component->name));
    }

    public function test_a_non_admin_cannot_write(): void
    {
        $regular = User::factory()->create();
        $category = ComponentCategory::factory()->create();
        $component = Component::factory()->create(['created_by' => User::factory()->create()->getKey()]);

        $this->actingAs($regular)->get(route('components.create'))->assertForbidden();
        $this->actingAs($regular)->post(route('components.store'), [
            'component_category_id' => $category->getKey(),
            'name' => 'Hack',
        ])->assertForbidden();
        $this->actingAs($regular)->put(route('components.update', $component), [
            'component_category_id' => $category->getKey(),
            'name' => 'Hack',
        ])->assertForbidden();
        $this->actingAs($regular)->delete(route('components.destroy', $component))->assertForbidden();
    }

    public function test_an_admin_can_create_a_component_with_a_datasheet(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_platform_admin' => true]);
        $category = ComponentCategory::factory()->create();

        $response = $this->actingAs($admin)->post(route('components.store'), [
            'component_category_id' => $category->getKey(),
            'name' => 'ESP32',
            'manufacturer' => 'Espressif',
            'specs' => [['key' => 'tension', 'value' => '3.3V']],
            'datasheet' => UploadedFile::fake()->create('datasheet.pdf', 100, 'application/pdf'),
        ]);

        $component = Component::query()->firstOrFail();
        $response->assertRedirect(route('components.show', $component));
        $this->assertDatabaseHas('components', ['id' => $component->getKey(), 'name' => 'ESP32']);
        $this->assertSame(['tension' => '3.3V'], $component->specs);
    }

    public function test_a_member_can_download_the_datasheet(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('components/fake/datasheet/sheet.pdf', 'contenu');

        $regular = User::factory()->create();
        $component = Component::factory()->create([
            'created_by' => User::factory()->create()->getKey(),
            'datasheet_disk' => 'local',
            'datasheet_path' => 'components/fake/datasheet/sheet.pdf',
            'datasheet_original_name' => 'sheet.pdf',
        ]);

        $this->actingAs($regular)
            ->get(route('components.datasheet', $component))
            ->assertOk();
    }

    public function test_an_admin_can_deactivate_and_delete_a_component(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $component = Component::factory()->create(['created_by' => $admin->getKey()]);

        $this->actingAs($admin)->patch(route('components.toggle-active', $component))->assertRedirect();
        $this->assertDatabaseHas('components', ['id' => $component->getKey(), 'is_active' => false]);

        $this->actingAs($admin)->delete(route('components.destroy', $component))->assertRedirect(route('components.index'));
        $this->assertDatabaseMissing('components', ['id' => $component->getKey()]);
    }

    public function test_a_project_local_component_never_appears_in_the_global_catalog(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->getKey()]);
        $local = Component::factory()->create(['created_by' => $owner->getKey(), 'owner_project_id' => $project->getKey()]);

        $this->actingAs($admin)
            ->get(route('components.index'))
            ->assertInertia(fn (Assert $page) => $page->component('Components/Index')->where('components', fn ($components) => collect($components)->doesntContain(fn ($c) => $c['id'] === $local->getKey())));

        $this->actingAs($admin)->get(route('components.show', $local))->assertNotFound();
        $this->actingAs($admin)->get(route('components.edit', $local))->assertNotFound();
        $this->actingAs($admin)->put(route('components.update', $local), ['component_category_id' => $local->component_category_id, 'name' => 'Hack'])->assertNotFound();
        $this->actingAs($admin)->delete(route('components.destroy', $local))->assertNotFound();
    }
}
