<?php

declare(strict_types=1);

namespace App\Core\DTO;

use Tizix\DataTransferObject\DataTransferObject;

class PaginationDTO extends DataTransferObject
{
    public int $per_page = 20;

    public int $page = 1;

    public ?string $sort_by = null;

    public string $sort_order = 'asc';
}
