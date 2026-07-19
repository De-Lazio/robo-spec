<?php

namespace Tests\Feature\Domain\Components;

use App\Models\ComponentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ComponentCategoryPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_read(): void
    {
        $regular = User::factory()->create();
        $category = ComponentCategory::factory()->create();

        $this->assertTrue(Gate::forUser($regular)->allows('viewAny', ComponentCategory::class));
        $this->assertTrue(Gate::forUser($regular)->allows('view', $category));
    }

    public function test_only_platform_admin_can_write(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $regular = User::factory()->create();
        $category = ComponentCategory::factory()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('create', ComponentCategory::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $category));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $category));

        $this->assertFalse(Gate::forUser($regular)->allows('create', ComponentCategory::class));
        $this->assertFalse(Gate::forUser($regular)->allows('update', $category));
        $this->assertFalse(Gate::forUser($regular)->allows('delete', $category));
    }
}
