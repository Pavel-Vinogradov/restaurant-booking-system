<?php

namespace Database\Factories;

use App\Domain\Restaurant\Enums\StaffRole;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Restaurant\Models\RestaurantStaff;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<RestaurantStaff> */
class RestaurantStaffFactory extends Factory
{
    protected $model = RestaurantStaff::class;

    public function definition(): array
    {
        return [
            'role' => StaffRole::Owner,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'restaurant_id' => Restaurant::factory(),
            'user_id' => User::factory(),
        ];
    }
}
