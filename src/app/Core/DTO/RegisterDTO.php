<?php

declare(strict_types=1);

namespace App\Core\DTO;

final class RegisterDTO extends BaseDTO
{
    public string $name;

    public string $email;

    public string $password;

    public ?string $phone = null;

    public ?string $telegram_id = null;
}
