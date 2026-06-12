<?php

declare(strict_types=1);

namespace App\Core\Request\Restaurant;

use App\Core\Request\ApiRequest;
use App\Domain\Restaurant\Models\Table;

/**
 * @property string|null $number Номер стола
 * @property int|null $capacity Вместимость
 * @property string|null $location Расположение
 * @property bool|null $is_available Доступен для бронирования
 * @property string|null $description Описание
 * @property float|null $min_order_amount Минимальная сумма заказа
 * @property array|null $features Особенности стола
 */
final class UpdateTableRequest extends ApiRequest
{
    public function authorize(): bool
    {
        $tableId = $this->route('table');
        $table = Table::find($tableId);

        if ($table === null) {
            return false;
        }

        return $this->user()->can('view', $table->restaurant);
    }

    public function rules(): array
    {
        return [
            'number' => ['sometimes', 'string', 'max:50'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'location' => ['nullable', 'string', 'max:100'],
            'is_available' => ['boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'capacity.min' => 'Вместимость должна быть не менее 1 человека.',
            'capacity.max' => 'Вместимость не может превышать 50 человек.',
        ];
    }
}
