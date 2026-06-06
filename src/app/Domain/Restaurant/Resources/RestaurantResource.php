<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Resources;

use App\Domain\Restaurant\Models\OpeningHour;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Restaurant\Models\RestaurantStaff;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Restaurant
 */
final class RestaurantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'opening_hours' => $this->whenLoaded('openingHours', function () {
                /** @phpstan-var Collection<int, OpeningHour> $openingHours */
                $openingHours = $this->openingHours;

                return $openingHours->map(fn (OpeningHour $hour) => [
                    'id' => $hour->id,
                    'day_of_week' => $hour->day_of_week,
                    'day_name' => $this->getDayName($hour->day_of_week),
                    'open_time' => $hour->open_time,
                    'close_time' => $hour->close_time,
                    'is_closed' => $hour->is_closed,
                ]);
            }),
            'staff' => $this->whenLoaded('staff', function () {
                /** @phpstan-var Collection<int, RestaurantStaff> $staff */
                $staff = $this->staff;

                return $staff->map(fn (RestaurantStaff $staff) => [
                    'id' => $staff->id,
                    'user_id' => $staff->user_id,
                    'role' => $staff->role,
                ]);
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getDayName(int $day): string
    {
        $days = [
            0 => 'monday',
            1 => 'tuesday',
            2 => 'wednesday',
            3 => 'thursday',
            4 => 'friday',
            5 => 'saturday',
            6 => 'sunday',
        ];

        return $days[$day] ?? 'unknown';
    }
}
