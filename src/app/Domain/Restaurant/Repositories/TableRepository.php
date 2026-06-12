<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Repositories;

use App\Core\DTO\PaginationDTO;
use App\Core\Repository\BaseRepository;
use App\Domain\Restaurant\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Table>
 */
class TableRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return Table::class;
    }

    /**
     * Получить столы ресторана с пагинацией
     *
     * @return Collection<int, Table>|LengthAwarePaginator<Table>
     */
    public function getByRestaurantId(int $restaurantId, ?PaginationDTO $pagination = null): Collection|LengthAwarePaginator
    {
        $query = $this->query()
            ->where('restaurant_id', $restaurantId)
            ->orderBy('number');

        if ($pagination !== null) {
            return $query->paginate(
                perPage: $pagination->per_page,
                page: $pagination->page
            );
        }

        return $query->get();
    }

    /**
     * Проверить, существует ли стол в ресторане
     */
    public function existsByRestaurantAndNumber(int $restaurantId, string $number): bool
    {
        /** @phpstan-ignore-next-line */
        return $this->query()
            ->where('restaurant_id', $restaurantId)
            ->where('number', $number)
            ->exists();
    }

    /**
     * Получить доступные столы ресторана
     *
     * @return Collection<int, Table>
     */
    public function getAvailableByRestaurantId(int $restaurantId): Collection
    {
        return $this->query()
            ->where('restaurant_id', $restaurantId)
            ->where('is_available', true)
            ->orderBy('number')
            ->get();
    }

    /**
     * Получить столы по вместимости
     *
     * @return Collection<int, Table>
     */
    public function getByCapacity(int $restaurantId, int $minCapacity, ?int $maxCapacity = null): Collection
    {
        $query = $this->query()
            ->where('restaurant_id', $restaurantId)
            ->where('capacity', '>=', $minCapacity)
            ->where('is_available', true);

        if ($maxCapacity !== null) {
            $query->where('capacity', '<=', $maxCapacity);
        }

        return $query->orderBy('capacity')->get();
    }
}
