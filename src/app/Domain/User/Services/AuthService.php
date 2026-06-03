<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Core\DTO\ChangePasswordDTO;
use App\Core\DTO\ForgotPasswordDTO;
use App\Core\DTO\LoginDTO;
use App\Core\DTO\RegisterDTO;
use App\Core\DTO\ResetPasswordDTO;
use App\Core\DTO\SendPhoneCodeDTO;
use App\Core\DTO\UpdateProfileDTO;
use App\Core\DTO\VerifyPhoneDTO;
use App\Core\Support\PhoneNormalizer;
use App\Domain\User\Exceptions\BlockedUserException;
use App\Domain\User\Models\User;
use App\Domain\User\Notifications\ResetPasswordNotification;
use App\Domain\User\Repositories\UserRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $user = $this->userRepository->findByEmailOrPhone($dto->login);

        if ($user === null || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.invalid_credentials')],
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
            'phone' => PhoneNormalizer::normalize($dto->phone),
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

    public function forgotPassword(ForgotPasswordDTO $dto): void
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if ($user === null) {
            return;
        }

        $token = bin2hex(random_bytes(32));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $dto->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $user->notify(new ResetPasswordNotification($token));
    }

    public function resetPassword(ResetPasswordDTO $dto): void
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $dto->email)
            ->first();

        if ($record === null || ! Hash::check($dto->token, $record->token)) {
            throw ValidationException::withMessages([
                'token' => ['Неверный или устаревший токен.'],
            ]);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            throw ValidationException::withMessages([
                'token' => ['Срок действия токена истёк.'],
            ]);
        }

        $user = $this->userRepository->findByEmail($dto->email);

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => ['Пользователь не найден.'],
            ]);
        }

        $user->password = Hash::make($dto->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $dto->email)->delete();
    }

    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        $attributes = [
            'name' => $dto->name,
            'email' => $dto->email,
        ];

        $normalizedPhone = PhoneNormalizer::normalize($dto->phone);

        if ($normalizedPhone !== $user->phone) {
            $attributes['phone'] = $normalizedPhone;
            $attributes['phone_verified_at'] = null;
        }

        $user->update($attributes);

        return $user->fresh();
    }

    public function changePassword(User $user, ChangePasswordDTO $dto): void
    {
        if (! Hash::check($dto->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Текущий пароль указан неверно.'],
            ]);
        }

        $user->password = Hash::make($dto->password);
        $user->save();
    }

    public function sendPhoneCode(SendPhoneCodeDTO $dto): string
    {
        $code = (string) random_int(100000, 999999);

        DB::table('phone_verification_codes')->updateOrInsert(
            ['phone' => $dto->phone],
            [
                'code' => Hash::make($code),
                'expires_at' => now()->addMinutes(10),
                'updated_at' => now(),
            ]
        );

        return $code;
    }

    public function verifyPhone(User $user, VerifyPhoneDTO $dto): void
    {
        $record = DB::table('phone_verification_codes')
            ->where('phone', $dto->phone)
            ->first();

        if ($record === null) {
            throw ValidationException::withMessages([
                'code' => ['Код не найден. Запросите новый код.'],
            ]);
        }

        if (Carbon::parse($record->expires_at)->isPast()) {
            throw ValidationException::withMessages([
                'code' => ['Срок действия кода истёк.'],
            ]);
        }

        if (! Hash::check($dto->code, $record->code)) {
            throw ValidationException::withMessages([
                'code' => ['Неверный код подтверждения.'],
            ]);
        }

        if ($user->phone !== $dto->phone) {
            $user->phone = $dto->phone;
        }

        $user->phone_verified_at = now();
        $user->save();

        DB::table('phone_verification_codes')->where('phone', $dto->phone)->delete();
    }
}
