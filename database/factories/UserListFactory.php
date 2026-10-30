<?php

namespace Database\Factories;

use App\Models\UserList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserList>
 */
class UserListFactory extends Factory
{
    protected $model = UserList::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'slug' => null,
            'description' => $this->faker->sentence(),
        ];
    }
}
