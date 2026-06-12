<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Models;

use Database\Factories\TableFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $restaurant_id
 * @property string $number
 * @property int $capacity
 * @property string|null $location
 * @property bool $is_available
 * @property string|null $description
 * @property float|null $min_order_amount
 * @property array|null $features
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Restaurant $restaurant
 */
#[Fillable(['restaurant_id', 'number', 'capacity', 'location', 'is_available', 'description', 'min_order_amount', 'features'])]
#[UseFactory(TableFactory::class)]
class Table extends Model
{
    use HasFactory;

    protected $casts = [
        'is_available' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'features' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    protected static function newFactory(): TableFactory
    {
        return TableFactory::new();
    }
}
