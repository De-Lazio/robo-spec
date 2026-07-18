<?php

namespace Database\Factories;

use App\Domain\Requirements\Enums\RequirementsStatus;
use App\Domain\Requirements\Support\RequirementsDefaults;
use App\Models\Project;
use App\Models\RequirementsDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequirementsDocument>
 */
class RequirementsDocumentFactory extends Factory
{
    public function definition(): array
    {
        $data = RequirementsDefaults::make();
        $data['step1']['projectName'] = fake()->words(3, true);
        $data['step1']['team'] = fake()->company();
        $data['step2']['context'] = fake()->paragraph();
        $data['step2']['problem'] = fake()->sentence();
        $data['step3']['missions'] = [fake()->sentence(), fake()->sentence()];
        $data['step9']['expectedResult'] = fake()->paragraph();
        $data['step9']['successCriteria'] = fake()->sentence();

        return [
            'project_id' => Project::factory(),
            'version' => 1,
            'status' => RequirementsStatus::Draft,
            'current_step' => 9,
            'data' => $data,
            'published_at' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => RequirementsStatus::Published,
            'published_at' => now(),
        ]);
    }
}
