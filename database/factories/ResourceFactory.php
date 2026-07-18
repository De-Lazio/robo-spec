<?php

namespace Database\Factories;

use App\Domain\Resources\Enums\ResourceCategory;
use App\Domain\Resources\Enums\ResourceKind;
use App\Models\Project;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<resource>
 */
class ResourceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word().'.pdf';

        return [
            'project_id' => Project::factory(),
            'uploaded_by' => User::factory(),
            'category' => fake()->randomElement(ResourceCategory::cases()),
            'kind' => ResourceKind::Document,
            'name' => $name,
            'original_name' => $name,
            'disk' => 'local',
            'path' => 'projects/fake/resources/fake/'.$name,
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1024, 5_000_000),
            'description' => fake()->optional()->sentence(),
            'checksum' => hash('sha256', $name),
        ];
    }
}
