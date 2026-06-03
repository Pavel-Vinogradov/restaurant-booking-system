<?php

declare(strict_types=1);

namespace App\Core\DTO;

final class LoginDTO extends BaseDTO
{
    public string $login;

    public string $password;
}
