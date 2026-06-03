<?php

declare(strict_types=1);

namespace App\Core\Request\Auth;

use App\Core\Request\ApiRequest;
use App\Core\Support\PhoneNormalizer;

/**
 * @property string $name Имя пользователя
 * @property string $email Email
 * @property string|null $phone Телефон
 */
final class UpdateProfileRequest extends ApiRequest
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
        /** @var \App\Domain\User\Models\User $user */
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users,phone,' . $user->id],
        ];
    }
}
