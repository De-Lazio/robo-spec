<?php

namespace Tests\Feature\Components;

use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ComponentCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_browse_categories(): void
    {
        $regular = User::factory()->create();
        ComponentCategory::factory()->create();

        $this->actingAs($regular)
            ->get(route('components.categories.index'))
            ->assertInertia(fn (Assert $page) => $page->component('Components/Categories/Index')->where('canManage', false));
    }

    public function test_a_non_admin_cannot_manage_categories(): void
    {
        $regular = User::factory()->create();
        $category = ComponentCategory::factory()->create();

        $this->actingAs($regular)->post(route('components.categories.store'), [
            'type' => 'sensor',
            'name' => 'Hack',
        ])->assertForbidden();

        $this->actingAs($regular)->put(route('components.categories.update', $category), ['name' => 'Hack'])->assertForbidden();
        $this->actingAs($regular)->delete(route('components.categories.destroy', $category))->assertForbidden();
    }

    public function test_an_admin_can_create_rename_and_delete_a_category(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);

        $this->actingAs($admin)->post(route('components.categories.store'), [
            'type' => 'sensor',
            'name' => 'Distance',
        ])->assertRedirect();

        $category = ComponentCategory::query()->firstOrFail();
        $this->assertSame('distance', $category->slug);

        $this->actingAs($admin)->put(route('components.categories.update', $category), ['name' => 'Proximité'])->assertRedirect();
        $this->assertDatabaseHas('component_categories', ['id' => $category->getKey(), 'name' => 'Proximité']);

        $this->actingAs($admin)->delete(route('components.categories.destroy', $category))->assertRedirect();
        $this->assertDatabaseMissing('component_categories', ['id' => $category->getKey()]);
    }

    public function test_deleting_a_category_with_components_is_rejected(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $category = ComponentCategory::factory()->create();
        Component::factory()->create(['component_category_id' => $category->getKey(), 'created_by' => $admin->getKey()]);

        $this->actingAs($admin)
            ->delete(route('components.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('component_categories', ['id' => $category->getKey()]);
    }
}
