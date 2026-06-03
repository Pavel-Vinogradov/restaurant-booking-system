<?php

declare(strict_types=1);

namespace App\Core\Request\Auth;

use App\Core\Request\ApiRequest;
use App\Core\Support\PhoneNormalizer;

/**
 * @property string $login Email или телефон пользователя
 * @property string $password Пароль пользователя
 */
final class LoginRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('login') && ! str_contains($this->input('login'), '@')) {
            $this->merge([
                'login' => PhoneNormalizer::normalize($this->input('login')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
