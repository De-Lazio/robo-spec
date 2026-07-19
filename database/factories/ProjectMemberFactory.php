<?php

namespace Database\Factories;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMember>
 */
class ProjectMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement([
                ProjectMemberRole::Manager,
                ProjectMemberRole::Mechanical,
                ProjectMemberRole::Electronics,
                ProjectMemberRole::Software,
                ProjectMemberRole::Contributor,
            ]),
            'joined_at' => now(),
        ];
    }
}
