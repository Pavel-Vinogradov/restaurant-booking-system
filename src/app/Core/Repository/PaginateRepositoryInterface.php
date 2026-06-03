<?php

declare(strict_types=1);

namespace App\Core\Repository;

use Illuminate\Pagination\LengthAwarePaginator;

interface PaginateRepositoryInterface
{
    /**
     * Метод для пагинации с сортировкой.
     *
     * @param  int  $perPage  Количество элементов на странице.
     * @param  int  $currentPage  Номер текущей страницы.
     * @param  string|null  $sortBy  Поле, по которому нужно сортировать (по умолчанию null).
     * @param  string  $sortOrder  Порядок сортировки (по умолчанию 'asc').
     */
    public function paginate(
        int $perPage = 20,
        int $currentPage = 1,
        ?string $sortBy = null,
        string $sortOrder = 'asc'
    ): LengthAwarePaginator;
}
