<?php

declare(strict_types=1);

namespace App\Core\DTO;

final class UpdateProfileDTO extends BaseDTO
{
    public string $name;

    public string $email;

    public ?string $phone = null;
}
