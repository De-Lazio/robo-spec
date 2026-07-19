<?php

namespace Database\Factories;

use App\Domain\Components\Enums\ComponentType;
use App\Models\ComponentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ComponentCategory>
 */
class ComponentCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'type' => fake()->randomElement(ComponentType::cases()),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
        ];
    }
}
