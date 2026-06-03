<?php

declare(strict_types=1);

namespace App\Core\DTO;

final class VerifyPhoneDTO extends BaseDTO
{
    public string $phone;

    public string $code;
}
