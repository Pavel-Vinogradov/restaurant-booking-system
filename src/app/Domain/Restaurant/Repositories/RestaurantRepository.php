<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Repositories;

use App\Core\DTO\PaginationDTO;
use App\Core\Repository\BaseRepository;
use App\Domain\Restaurant\DTOs\CreateRestaurantDTO;
use App\Domain\Restaurant\DTOs\UpdateRestaurantDTO;
use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @extends BaseRepository<Restaurant>
 *
 * @method Restaurant create(array $attributes)
 */
class RestaurantRepository extends BaseRepository
{
    public function __construct(
        private readonly RestaurantStaffRepository $staffRepository,
        private readonly OpeningHourRepository $openingHourRepository,
    ) {
        parent::__construct();
    }

    protected function getModel(): string
    {
        return Restaurant::class;
    }

    /**
     * Получить рестораны, где пользователь является сотрудником
     *
     * @return Collection<int, Restaurant>|LengthAwarePaginator<int, Restaurant>
     */
    public function getByUserId(int $userId, ?PaginationDTO $pagination = null): Collection|LengthAwarePaginator
    {
        $query = $this->query()
            ->whereHas('staff', function ($query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->with(['staff', 'openingHours']);

        if ($pagination !== null) {
            return $query->paginate(
                perPage: $pagination->per_page,
                page: $pagination->page
            );
        }

        return $query->get();
    }

    /**
     * @phpstan-return Restaurant|null
     */
    public function findByIdWithRelations(int $id, array $relations = []): ?Restaurant
    {
        /** @var Restaurant|null $restaurant */
        $restaurant = $this->query()
            ->with($relations)
            ->find($id);

        return $restaurant;
    }

    /**
     * Создать ресторан с owner и часами работы в транзакции
     *
     * @throws Throwable
     */
    public function createWithRelations(CreateRestaurantDTO $dto, int $ownerId): Restaurant
    {
        return DB::transaction(function () use ($dto, $ownerId) {
            $restaurant = $this->query()->create([
                'name' => $dto->name,
                'address' => $dto->address,
                'phone' => $dto->phone,
                'timezone' => $dto->timezone,
                'status' => $dto->status,
            ]);

            $this->staffRepository->createOwner($restaurant->id, $ownerId);

            if ($dto->opening_hours !== null) {
                $this->openingHourRepository->syncForRestaurant(
                    $restaurant->id,
                    $dto->opening_hours
                );
            }

            return $restaurant->fresh(['staff', 'openingHours']);
        });
    }

    /**
     * Обновить ресторан с часами работы в транзакции
     *
     * @throws Throwable
     */
    public function updateWithRelations(int $id, UpdateRestaurantDTO $dto): ?Restaurant
    {
        return DB::transaction(function () use ($id, $dto) {
            $restaurant = $this->update($id, array_filter([
                'name' => $dto->name,
                'address' => $dto->address,
                'phone' => $dto->phone,
                'timezone' => $dto->timezone,
                'status' => $dto->status,
            ], fn ($value) => $value !== null));

            if ($restaurant === null) {
                return null;
            }

            if ($dto->opening_hours !== null) {
                $this->openingHourRepository->syncForRestaurant(
                    $restaurant->id,
                    $dto->opening_hours
                );
            }

            return $restaurant->fresh(['staff', 'openingHours']);
        });
    }
}
