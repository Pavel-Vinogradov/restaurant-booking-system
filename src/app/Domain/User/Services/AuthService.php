<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Core\DTO\LoginDTO;
use App\Core\DTO\RegisterDTO;
use App\Domain\User\Exceptions\BlockedUserException;
use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

readonly class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * @throws BlockedUserException
     */
    public function login(LoginDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if ($user === null || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.invalid_credentials')],
            ]);
        }

        if (! $user->is_active) {
            throw new BlockedUserException;
        }

        $token = $user->createToken('api')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function register(RegisterDTO $dto): array
    {
        $user = $this->userRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'phone' => $dto->phone,
            'telegram_id' => $dto->telegram_id,
            'is_active' => true,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function logout(User $user): void
    {
        /** @phpstan-ignore-next-line */
        $user->tokens()->delete();
    }
}
