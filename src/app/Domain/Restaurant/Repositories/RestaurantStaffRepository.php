<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Repositories;

use App\Core\Repository\BaseRepository;
use App\Domain\Restaurant\Enums\StaffRole;
use App\Domain\Restaurant\Models\RestaurantStaff;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<RestaurantStaff>
 *
 * @method RestaurantStaff create(array $attributes)
 */
class RestaurantStaffRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return RestaurantStaff::class;
    }

    /**
     * Получить сотрудников ресторана
     *
     * @return Collection<int, RestaurantStaff>
     */
    public function getByRestaurantId(int $restaurantId): Collection
    {
        return $this->query()
            ->where('restaurant_id', $restaurantId)
            ->with('user')
            ->get();
    }

    /**
     * Проверить, является ли пользователь сотрудником ресторана
     */
    public function existsByRestaurantAndUser(int $restaurantId, int $userId): bool
    {
        /** @phpstan-ignore-next-line */
        return $this->query()
            ->where('restaurant_id', $restaurantId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Найти запись сотрудника
     */
    public function findByRestaurantAndUser(int $restaurantId, int $userId): ?RestaurantStaff
    {
        return $this->query()
            ->where('restaurant_id', $restaurantId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Создать владельца ресторана
     */
    public function createOwner(int $restaurantId, int $userId): RestaurantStaff
    {
        return $this->query()->create([
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'role' => StaffRole::Owner->value,
        ]);
    }

    /**
     * Создать администратора ресторана
     */
    public function createAdmin(int $restaurantId, int $userId): RestaurantStaff
    {
        return $this->query()->create([
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'role' => StaffRole::Admin->value,
        ]);
    }

    /**
     * Создать хостесс ресторана
     */
    public function createHostess(int $restaurantId, int $userId): RestaurantStaff
    {
        return $this->query()->create([
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'role' => StaffRole::Hostess->value,
        ]);
    }

    /**
     * Обновить роль сотрудника
     */
    public function updateRole(int $restaurantId, int $userId, StaffRole $role): bool
    {
        $staff = $this->findByRestaurantAndUser($restaurantId, $userId);

        if ($staff === null) {
            return false;
        }

        return $staff->update(['role' => $role->value]);
    }

    /**
     * Удалить сотрудника из ресторана
     */
    public function deleteByRestaurantAndUser(int $restaurantId, int $userId): bool
    {
        $staff = $this->findByRestaurantAndUser($restaurantId, $userId);

        if ($staff === null) {
            return false;
        }

        return $staff->delete();
    }
}
