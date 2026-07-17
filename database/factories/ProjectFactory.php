<?php

namespace Database\Factories;

use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Projects\Enums\RobotType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'owner_id' => User::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->sentence(),
            'robot_type' => fake()->randomElement(RobotType::cases()),
            'domain' => fake()->randomElement(['Agriculture', 'Éducation', 'Industrie']),
            'status' => ProjectStatus::Draft,
            'progress' => 0,
        ];
    }
}
