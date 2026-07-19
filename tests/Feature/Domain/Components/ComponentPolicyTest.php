<?php

namespace Tests\Feature\Domain\Components;

use App\Models\Component;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ComponentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_read(): void
    {
        $regular = User::factory()->create();
        $component = Component::factory()->create(['created_by' => User::factory()->create()->getKey()]);

        $this->assertTrue(Gate::forUser($regular)->allows('viewAny', Component::class));
        $this->assertTrue(Gate::forUser($regular)->allows('view', $component));
    }

    public function test_only_platform_admin_can_write(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $regular = User::factory()->create();
        $component = Component::factory()->create(['created_by' => $admin->getKey()]);

        $this->assertTrue(Gate::forUser($admin)->allows('create', Component::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $component));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $component));

        $this->assertFalse(Gate::forUser($regular)->allows('create', Component::class));
        $this->assertFalse(Gate::forUser($regular)->allows('update', $component));
        $this->assertFalse(Gate::forUser($regular)->allows('delete', $component));
    }
}
