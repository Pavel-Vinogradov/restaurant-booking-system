<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'timezone' => 'Europe/Moscow',
            'status' => 'active',
        ];
    }
}
