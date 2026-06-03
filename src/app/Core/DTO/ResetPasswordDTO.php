<?php

declare(strict_types=1);

namespace App\Core\DTO;

final class ResetPasswordDTO extends BaseDTO
{
    public string $email;

    public string $token;

    public string $password;
}
