<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\DTOs;

use App\Core\DTO\BaseDTO;

final class UpdateTableDTO extends BaseDTO
{
    public ?string $number = null;

    public ?int $capacity = null;

    public ?string $location = null;

    public ?bool $is_available = null;

    public ?string $description = null;

    public ?float $min_order_amount = null;

    public ?array $features = null;
}
