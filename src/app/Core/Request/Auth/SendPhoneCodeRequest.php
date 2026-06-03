<?php

declare(strict_types=1);

namespace App\Core\Request\Auth;

use App\Core\Request\ApiRequest;
use App\Core\Support\PhoneNormalizer;

/**
 * @property string $phone Номер телефона для верификации
 */
final class SendPhoneCodeRequest extends ApiRequest
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
            'phone' => ['required', 'string', 'max:255'],
        ];
    }
}
