<?php

declare(strict_types=1);

namespace App\Core\Request\Restaurant;

use App\Core\DTO\PaginationDTO;
use App\Core\Request\Shared\PaginationRequest;
use Tizix\DataTransferObject\Exceptions\UnknownProperties;
use Tizix\DataTransferObject\Exceptions\ValidationException;

/**
 * @property int|null $page Номер страницы
 * @property int|null $per_page Количество на странице
 */
final class RestaurantIndexRequest extends PaginationRequest
{
    /**
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function toDTO(): PaginationDTO
    {
        return new PaginationDTO($this->validated());
    }
}
