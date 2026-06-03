<?php

declare(strict_types=1);

namespace App\Core\DTO;

final class ChangePasswordDTO extends BaseDTO
{
    public string $current_password;

    public string $password;
}
