<?php

declare(strict_types=1);

namespace App\Core\Request\Restaurant;

use App\Core\Request\ApiRequest;
use App\Domain\Restaurant\Models\Restaurant;

/**
 * @property int $restaurant_id ID ресторана
 * @property string $number Номер стола
 * @property int $capacity Вместимость
 * @property string|null $location Расположение
 * @property bool $is_available Доступен для бронирования
 * @property string|null $description Описание
 * @property float|null $min_order_amount Минимальная сумма заказа
 * @property array|null $features Особенности стола
 */
final class StoreTableRequest extends ApiRequest
{
    public function authorize(): bool
    {
        $restaurantId = $this->input('restaurant_id');
        $restaurant = Restaurant::find($restaurantId);

        if ($restaurant === null) {
            return false;
        }

        return $this->user()->can('view', $restaurant);
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'number' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
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
            'restaurant_id.required' => 'ID ресторана обязателен.',
            'restaurant_id.exists' => 'Ресторан не найден.',
            'number.required' => 'Номер стола обязателен.',
            'capacity.required' => 'Вместимость обязательна.',
            'capacity.min' => 'Вместимость должна быть не менее 1 человека.',
            'capacity.max' => 'Вместимость не может превышать 50 человек.',
        ];
    }
}
