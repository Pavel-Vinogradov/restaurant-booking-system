<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\DTOs;

use App\Core\DTO\BaseDTO;

/**
 * DTO для обновления ресторана
 *
 * @property string|null $name Название ресторана
 * @property string|null $address Адрес ресторана
 * @property string|null $phone Контактный телефон
 * @property string|null $timezone Часовой пояс
 * @property string|null $status Статус (active/inactive)
 * @property array|null $opening_hours Часы работы
 */
final class UpdateRestaurantDTO extends BaseDTO
{
    public ?string $name = null;

    public ?string $address = null;

    public ?string $phone = null;

    public ?string $timezone = null;

    public ?string $status = null;

    /** @var array<int, array{day_of_week: int, open_time: string, close_time: string, is_closed?: bool}>|null */
    public ?array $opening_hours = null;
}
