<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\DTOs;

use App\Core\DTO\BaseDTO;

final class CreateTableDTO extends BaseDTO
{
    public int $restaurant_id;

    public string $number;

    public int $capacity;

    public ?string $location = null;

    public bool $is_available = true;

    public ?string $description = null;

    public ?float $min_order_amount = null;

    public ?array $features = null;
}
