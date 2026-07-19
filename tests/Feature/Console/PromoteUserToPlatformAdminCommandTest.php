<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoteUserToPlatformAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_promotes_an_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('user:promote-admin', ['email' => 'admin@example.com'])
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->is_platform_admin);
    }

    public function test_it_fails_clearly_for_an_unknown_email(): void
    {
        $this->artisan('user:promote-admin', ['email' => 'missing@example.com'])
            ->assertExitCode(1);
    }

    public function test_it_is_idempotent_for_an_already_promoted_user(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com', 'is_platform_admin' => true]);

        $this->artisan('user:promote-admin', ['email' => 'admin@example.com'])
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->is_platform_admin);
    }
}
