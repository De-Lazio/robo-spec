<?php

namespace Database\Factories;

use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Component>
 */
class ComponentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'component_category_id' => ComponentCategory::factory(),
            'name' => fake()->words(2, true),
            'manufacturer' => fake()->company(),
            'reference' => mb_strtoupper(fake()->bothify('??-####')),
            'description' => fake()->sentence(),
            'specs' => ['tension' => '5V', 'interface' => 'I2C'],
            'price_cents' => fake()->numberBetween(100, 15_000),
            'currency' => 'EUR',
            'supplier_url' => fake()->url(),
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
