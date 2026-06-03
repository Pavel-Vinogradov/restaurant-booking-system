<?php

declare(strict_types=1);

namespace App\Core\Request\Auth;

use App\Core\Request\ApiRequest;
use App\Core\Support\PhoneNormalizer;

/**
 * @property string $name Полное имя пользователя
 * @property string $email Email пользователя
 * @property string $password Пароль пользователя (минимум 8 символов)
 * @property string|null $phone Номер телефона
 * @property string|null $telegram_id ID в Telegram
 */
final class RegisterRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => PhoneNormalizer::normalize($this->input('phone')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users'],
            'telegram_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
