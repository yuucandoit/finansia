<?php

namespace Database\Factories;

use App\Models\FuturePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FuturePlan>
 */
class FuturePlanFactory extends Factory
{
    protected $model = FuturePlan::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'title'       => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'target_date' => now()->addMonths(rand(3, 36)),
            'status'      => FuturePlan::STATUS_PLANNED,
            'color'       => 'indigo',
        ];
    }
}
