<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Models;

use App\Domain\Restaurant\Enums\RestaurantStatus;
use Database\Factories\RestaurantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $timezone
 * @property RestaurantStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'address', 'phone', 'timezone', 'status'])]
#[UseFactory(RestaurantFactory::class)]
class Restaurant extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => RestaurantStatus::class,
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(RestaurantStaff::class);
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHour::class);
    }

    protected static function newFactory(): RestaurantFactory
    {
        return RestaurantFactory::new();
    }
}
