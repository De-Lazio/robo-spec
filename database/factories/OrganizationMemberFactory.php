<?php

namespace Database\Factories;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationMember>
 */
class OrganizationMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement([OrganizationRole::Admin, OrganizationRole::Member]),
            'joined_at' => now(),
        ];
    }
}
