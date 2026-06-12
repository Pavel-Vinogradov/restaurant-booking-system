<?php

declare(strict_types=1);

namespace App\Core\Request\Restaurant;

use App\Core\Request\Shared\PaginationRequest;
use App\Domain\Restaurant\Models\Restaurant;

/**
 * @property int|null $page Номер страницы
 * @property int|null $per_page Количество на странице
 * @property string|null $sort_by Поле для сортировки
 * @property string|null $sort_order Направление сортировки (asc/desc)
 * @property bool|null $available_only Только доступные столы
 * @property int|null $min_capacity Минимальная вместимость
 * @property int|null $max_capacity Максимальная вместимость
 */
final class TableIndexRequest extends PaginationRequest
{
    public function authorize(): bool
    {
        $restaurantId = $this->route('restaurant_id');
        $restaurant = Restaurant::find($restaurantId);

        if ($restaurant === null) {
            return false;
        }

        return $this->user()->can('view', $restaurant);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'available_only' => ['nullable', 'boolean'],
            'min_capacity' => ['nullable', 'integer', 'min:1'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
