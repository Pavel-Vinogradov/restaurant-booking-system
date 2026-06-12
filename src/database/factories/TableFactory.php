<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Restaurant\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    /**
     * @var class-string<Table>
     */
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'number' => $this->faker->unique()->bothify('T##'),
            'capacity' => $this->faker->numberBetween(2, 8),
            'location' => $this->faker->randomElement(['Зал', 'Веранда', 'VIP-зал', 'У окна']),
            'is_available' => $this->faker->boolean(80), // 80% chance to be available
            'description' => $this->faker->optional()->sentence(),
            'min_order_amount' => $this->faker->optional(0.3)->randomFloat(2, 1000, 10000),
            'features' => $this->faker->optional(0.5)->randomElements(
                ['window', 'quiet', 'vip', 'outdoor', 'accessible'],
                $this->faker->numberBetween(0, 2)
            ),
        ];
    }

    /**
     * Создать стол с определенной вместимостью
     */
    public function capacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $capacity,
        ]);
    }

    /**
     * Создать недоступный стол
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    /**
     * Создать VIP стол
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => 'VIP-зал',
            'features' => ['vip', 'quiet'],
            'min_order_amount' => $this->faker->randomFloat(2, 5000, 20000),
        ]);
    }
}
