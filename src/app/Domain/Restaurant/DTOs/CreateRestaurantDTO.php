<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\DTOs;

use App\Core\DTO\BaseDTO;

/**
 * DTO для создания ресторана
 *
 * @property string $name Название ресторана
 * @property string $address Адрес ресторана
 * @property string $phone Контактный телефон
 * @property string $timezone Часовой пояс (по умолчанию Europe/Moscow)
 * @property string $status Статус (active/inactive)
 * @property array|null $opening_hours Часы работы
 */
final class CreateRestaurantDTO extends BaseDTO
{
    public string $name;

    public string $address;

    public string $phone;

    public string $timezone = 'Europe/Moscow';

    public string $status = 'active';

    /** @var array<int, array{day_of_week: int, open_time: string, close_time: string, is_closed?: bool}>|null */
    public ?array $opening_hours = null;
}
