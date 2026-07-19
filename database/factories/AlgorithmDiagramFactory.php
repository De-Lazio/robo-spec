<?php

namespace Database\Factories;

use App\Domain\AlgorithmDiagrams\Enums\DiagramFormalism;
use App\Models\AlgorithmDiagram;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlgorithmDiagram>
 */
class AlgorithmDiagramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->words(3, true),
            'formalism' => DiagramFormalism::Algorigramme,
            'data' => ['nodes' => [], 'edges' => [], 'variables' => []],
            'created_by' => User::factory(),
        ];
    }
}
