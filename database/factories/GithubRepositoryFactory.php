<?php

namespace Database\Factories;

use App\Domain\Integrations\GitHub\Enums\SyncStatus;
use App\Models\GithubRepository;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GithubRepository>
 */
class GithubRepositoryFactory extends Factory
{
    public function definition(): array
    {
        $owner = fake()->userName();
        $repository = fake()->slug(2);

        return [
            'project_id' => Project::factory(),
            'provider' => 'github',
            'owner' => $owner,
            'repository' => $repository,
            'url' => "https://github.com/{$owner}/{$repository}",
            'default_branch' => 'main',
            'visibility' => 'public',
            'last_synced_at' => now(),
            'sync_status' => SyncStatus::Synced,
            'metadata' => [
                'description' => fake()->sentence(),
                'stars' => fake()->numberBetween(0, 500),
                'forks' => fake()->numberBetween(0, 100),
                'open_issues' => fake()->numberBetween(0, 20),
                'language' => fake()->randomElement(['PHP', 'Python', 'C++', 'JavaScript']),
                'branches' => ['main', 'develop'],
                'commits' => [
                    ['sha' => mb_substr(fake()->sha1(), 0, 7), 'message' => fake()->sentence(), 'author' => fake()->name(), 'date' => now()->subDays(2)->toIso8601String()],
                ],
                'error' => null,
            ],
        ];
    }
}
