<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Services;

use App\Core\DTO\PaginationDTO;
use App\Core\Service\BaseService;
use App\Domain\Restaurant\DTOs\CreateRestaurantDTO;
use App\Domain\Restaurant\DTOs\UpdateRestaurantDTO;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Restaurant\Repositories\RestaurantRepository;
use App\Domain\Restaurant\Repositories\RestaurantStaffRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class RestaurantService extends BaseService
{
    public function __construct(
        private readonly RestaurantRepository $repository,
        private readonly RestaurantStaffRepository $staffRepository,
    ) {}

    protected function getRepository(): RestaurantRepository
    {
        return $this->repository;
    }

    /**
     * Получить все рестораны пользователя
     *
     * @return Collection<int, Restaurant>|LengthAwarePaginator<int, Restaurant>
     */
    public function getUserRestaurants(int $userId, ?PaginationDTO $pagination = null): Collection|LengthAwarePaginator
    {
        return $this->repository->getByUserId($userId, $pagination);
    }

    /**
     * Создать ресторан и назначить создателя владельцем
     *
     * @throws Throwable
     */
    public function createWithOwner(CreateRestaurantDTO $dto, int $ownerId): Restaurant
    {
        return $this->repository->createWithRelations($dto, $ownerId);
    }

    /**
     * Обновить ресторан с часами работы
     *
     * @throws Throwable
     */
    public function updateWithHours(int $id, UpdateRestaurantDTO $dto): ?Restaurant
    {
        return $this->repository->updateWithRelations($id, $dto);
    }

    /**
     * Проверить, имеет ли пользователь доступ к ресторану
     */
    public function userHasAccess(int $restaurantId, int $userId): bool
    {
        return $this->staffRepository->existsByRestaurantAndUser(
            $restaurantId,
            $userId
        );
    }

    /**
     * Найти ресторан по ID со связями (staff, openingHours)
     */
    public function findByIdWithRelations(int $id): ?Restaurant
    {
        return $this->repository->findByIdWithRelations($id, ['staff', 'openingHours']);
    }

    /**
     * Проверить, существует ли ресторан
     */
    public function exists(int $id): bool
    {
        return $this->repository->findById($id) !== null;
    }
}
