<?php

declare(strict_types=1);

namespace App\Core\Request\Restaurant;

use App\Core\Request\ApiRequest;

/**
 * @property string|null $name Название ресторана
 * @property string|null $address Адрес ресторана
 * @property string|null $phone Контактный телефон
 * @property string|null $timezone Часовой пояс
 * @property string|null $status Статус (active/inactive)
 * @property array|null $opening_hours Часы работы
 */
final class UpdateRestaurantRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'opening_hours' => ['nullable', 'array'],
            'opening_hours.*.day_of_week' => ['required_with:opening_hours', 'integer', 'between:0,6'],
            'opening_hours.*.open_time' => ['required_with:opening_hours', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'opening_hours.*.close_time' => ['required_with:opening_hours', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'opening_hours.*.is_closed' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'opening_hours.*.day_of_week.between' => 'День недели должен быть от 0 (понедельник) до 6 (воскресенье).',
            'opening_hours.*.open_time.regex' => 'Время открытия должно быть в формате HH:MM.',
            'opening_hours.*.close_time.regex' => 'Время закрытия должно быть в формате HH:MM.',
        ];
    }
}
