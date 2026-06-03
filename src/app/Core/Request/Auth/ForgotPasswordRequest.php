<?php

declare(strict_types=1);

namespace App\Core\Request\Auth;

use App\Core\Request\ApiRequest;

/**
 * @property string $email Email пользователя
 */
final class ForgotPasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
        ];
    }
}
