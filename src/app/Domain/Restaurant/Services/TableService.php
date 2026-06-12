<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Services;

use App\Core\DTO\PaginationDTO;
use App\Core\Service\BaseService;
use App\Domain\Restaurant\DTOs\CreateTableDTO;
use App\Domain\Restaurant\DTOs\UpdateTableDTO;
use App\Domain\Restaurant\Models\Table;
use App\Domain\Restaurant\Repositories\RestaurantRepository;
use App\Domain\Restaurant\Repositories\RestaurantStaffRepository;
use App\Domain\Restaurant\Repositories\TableRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * @extends BaseService<Table>
 */
class TableService extends BaseService
{
    public function __construct(
        private readonly TableRepository $tableRepository,
        private readonly RestaurantRepository $restaurantRepository,
        private readonly RestaurantStaffRepository $staffRepository,
    ) {}

    protected function getRepository(): TableRepository
    {
        return $this->tableRepository;
    }

    /**
     * Получить столы ресторана с пагинацией
     *
     * @return Collection<int, Table>|LengthAwarePaginator<Table>
     */
    public function getRestaurantTables(int $restaurantId, ?PaginationDTO $pagination = null): Collection|LengthAwarePaginator
    {
        return $this->tableRepository->getByRestaurantId($restaurantId, $pagination);
    }

    /**
     * Создать новый стол
     *
     * @throws Throwable
     */
    public function createTable(CreateTableDTO $dto): Table
    {
        if ($this->tableRepository->existsByRestaurantAndNumber($dto->restaurant_id, $dto->number)) {
            throw new \InvalidArgumentException("Стол с номером {$dto->number} уже существует в этом ресторане.");
        }

        return $this->tableRepository->create([
            'restaurant_id' => $dto->restaurant_id,
            'number' => $dto->number,
            'capacity' => $dto->capacity,
            'location' => $dto->location,
            'is_available' => $dto->is_available,
            'description' => $dto->description,
            'min_order_amount' => $dto->min_order_amount,
            'features' => $dto->features,
        ]);
    }

    /**
     * Обновить стол
     *
     * @throws Throwable
     */
    public function updateTable(int $id, UpdateTableDTO $dto): ?Table
    {
        $table = $this->tableRepository->findById($id);

        if ($table === null) {
            return null;
        }

        // Если обновляем номер, проверяем уникальность
        if ($dto->number !== null && $dto->number !== $table->number && $this->tableRepository->existsByRestaurantAndNumber($table->restaurant_id, $dto->number)) {
            throw new \InvalidArgumentException("Стол с номером {$dto->number} уже существует в этом ресторане.");
        }

        $updateData = array_filter([
            'number' => $dto->number,
            'capacity' => $dto->capacity,
            'location' => $dto->location,
            'is_available' => $dto->is_available,
            'description' => $dto->description,
            'min_order_amount' => $dto->min_order_amount,
            'features' => $dto->features,
        ], static fn ($value) => $value !== null);

        return $this->tableRepository->update($id, $updateData);
    }

    /**
     * Получить доступные столы ресторана
     *
     * @return Collection<int, Table>
     */
    public function getAvailableTables(int $restaurantId): Collection
    {
        return $this->tableRepository->getAvailableByRestaurantId($restaurantId);
    }

    /**
     * Получить столы по вместимости
     *
     * @return Collection<int, Table>
     */
    public function getTablesByCapacity(int $restaurantId, int $minCapacity, ?int $maxCapacity = null): Collection
    {
        return $this->tableRepository->getByCapacity($restaurantId, $minCapacity, $maxCapacity);
    }

    /**
     * Проверить, имеет ли пользователь доступ к ресторану
     */
    public function userHasAccessToRestaurant(int $restaurantId, int $userId): bool
    {
        return $this->staffRepository->existsByRestaurantAndUser($restaurantId, $userId);
    }

    /**
     * Проверить, имеет ли пользователь доступ к столу (через ресторан)
     */
    public function userHasAccessToTable(int $tableId, int $userId): bool
    {
        $table = $this->tableRepository->findById($tableId);

        if ($table === null) {
            return false;
        }

        return $this->staffRepository->existsByRestaurantAndUser($table->restaurant_id, $userId);
    }

    /**
     * Проверить, существует ли стол
     */
    public function exists(int $id): bool
    {
        return $this->tableRepository->findById($id) !== null;
    }
}
