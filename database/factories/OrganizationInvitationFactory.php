<?php

namespace Database\Factories;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrganizationInvitation>
 */
class OrganizationInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => fake()->randomElement([OrganizationRole::Admin, OrganizationRole::Member]),
            'token' => hash('sha256', Str::random(40)),
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
            'invited_by' => User::factory(),
        ];
    }
}
