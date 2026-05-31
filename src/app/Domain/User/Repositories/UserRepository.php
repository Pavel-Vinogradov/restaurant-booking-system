<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Core\Repository\BaseRepository;
use App\Domain\User\Models\User;

class UserRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }
}
