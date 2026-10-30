<?php

namespace Database\Factories;

use App\Models\Action;
use App\Models\ActionRoleOverride;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActionRoleOverride>
 */
class ActionRoleOverrideFactory extends Factory
{
    public function definition(): array
    {
        return [
            'action_id' => Action::factory(),
            'role_id' => Role::factory(),
            'active' => $this->faker->boolean(),
        ];
    }
}
