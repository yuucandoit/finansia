<?php

namespace Database\Factories;

use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FuturePlanItem>
 */
class FuturePlanItemFactory extends Factory
{
    protected $model = FuturePlanItem::class;

    public function definition(): array
    {
        return [
            'future_plan_id' => FuturePlan::factory(),
            'parent_id'      => null,
            'title'          => $this->faker->words(3, true),
            'quantity'       => $this->faker->numberBetween(1, 5),
            'unit_price'     => $this->faker->numberBetween(100000, 5000000),
            'is_completed'   => false,
            'notes'          => $this->faker->sentence(),
            'order'          => 1,
        ];
    }
}

