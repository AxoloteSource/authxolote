<?php

namespace Database\Factories;

use App\Enums\MenuItemType;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'type' => MenuItemType::Link,
            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
            'route' => $this->faker->optional()->slug(),
            'path' => '/'.$this->faker->slug(),
            'icon' => $this->faker->optional()->word(),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'active' => true,
        ];
    }

    public function header(): static
    {
        return $this->state([
            'type' => MenuItemType::Header,
            'route' => null,
            'path' => null,
        ]);
    }

    public function link(): static
    {
        return $this->state([
            'type' => MenuItemType::Link,
        ]);
    }

    public function section(): static
    {
        return $this->state([
            'type' => MenuItemType::Section,
            'route' => null,
            'path' => null,
        ]);
    }
}
