<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\User\Models\User;

class RestaurantPolicy
{
    /**
     * Проверить, имеет ли пользователь доступ к ресторану (через staff)
     */
    public function view(User $user, Restaurant $restaurant): bool
    {
        return $restaurant->staff()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Проверить, может ли пользователь обновлять ресторан
     */
    public function update(User $user, Restaurant $restaurant): bool
    {
        return $this->view($user, $restaurant);
    }

    /**
     * Проверить, может ли пользователь удалять ресторан
     */
    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $this->view($user, $restaurant);
    }
}
