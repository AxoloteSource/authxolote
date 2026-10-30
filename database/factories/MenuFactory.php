<?php

namespace Database\Factories;

use App\Enums\MenuSize;
use App\Models\Application;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
            'icon' => $this->faker->optional()->word(),
            'size' => MenuSize::Md,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'active' => true,
        ];
    }
}
