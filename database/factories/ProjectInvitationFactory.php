<?php

namespace Database\Factories;

use App\Domain\Projects\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectInvitation>
 */
class ProjectInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => fake()->randomElement([ProjectMemberRole::Contributor, ProjectMemberRole::Viewer]),
            'token' => hash('sha256', Str::random(40)),
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
            'invited_by' => User::factory(),
        ];
    }
}
