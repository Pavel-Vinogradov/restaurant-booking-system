<?php

declare(strict_types=1);

namespace App\Core\Request\Shared;

use App\Core\DTO\PaginationDTO;
use App\Core\Request\ApiRequest;
use Tizix\DataTransferObject\Exceptions\UnknownProperties;
use Tizix\DataTransferObject\Exceptions\ValidationException;

/**
 * @property int|null $page Номер страницы
 * @property int|null $per_page Количество на странице
 * @property string|null $sort_by Поле для сортировки
 * @property string|null $sort_order Направление сортировки (asc/desc)
 */
abstract class PaginationRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * @throws UnknownProperties
     * @throws ValidationException
     */
    public function toDTO(): PaginationDTO
    {
        return new PaginationDTO($this->validated());
    }
}
