<?php

namespace Database\Factories;

use App\Models\Snake;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Snake>
 */
class SnakeFactory extends Factory
{
    protected $model = Snake::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->firstName(),
            'feeding_interval_days' => 10,
            'status' => 'active',
        ];
    }
}
