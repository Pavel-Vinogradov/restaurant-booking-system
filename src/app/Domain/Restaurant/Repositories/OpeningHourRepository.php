<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Repositories;

use App\Core\Repository\BaseRepository;
use App\Domain\Restaurant\Models\OpeningHour;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<OpeningHour>
 *
 * @method OpeningHour create(array $attributes)
 */
class OpeningHourRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return OpeningHour::class;
    }

    /**
     * Получить часы работы ресторана
     *
     * @return Collection<int, OpeningHour>
     */
    public function getByRestaurantId(int $restaurantId): Collection
    {
        /** @phpstan-ignore-next-line */
        return $this->query()
            ->where('restaurant_id', $restaurantId)
            ->orderBy('day_of_week')
            ->get();
    }

    /**
     * Удалить все часы работы ресторана
     */
    public function deleteByRestaurantId(int $restaurantId): void
    {
        $this->query()
            ->where('restaurant_id', $restaurantId)
            ->delete();
    }

    /**
     * Создать несколько записей часов работы
     *
     * @param  array<int, array{day_of_week: int, open_time: string, close_time: string, is_closed?: bool}>  $hours
     */
    public function createMany(int $restaurantId, array $hours): void
    {
        foreach ($hours as $hour) {
            $this->query()->create([
                'restaurant_id' => $restaurantId,
                'day_of_week' => $hour['day_of_week'],
                'open_time' => $hour['open_time'],
                'close_time' => $hour['close_time'],
                'is_closed' => $hour['is_closed'] ?? false,
            ]);
        }
    }

    /**
     * Синхронизировать часы работы (удалить старые, создать новые)
     *
     * @param  array<int, array{day_of_week: int, open_time: string, close_time: string, is_closed?: bool}>  $hours
     */
    public function syncForRestaurant(int $restaurantId, array $hours): void
    {
        $this->deleteByRestaurantId($restaurantId);
        $this->createMany($restaurantId, $hours);
    }
}
