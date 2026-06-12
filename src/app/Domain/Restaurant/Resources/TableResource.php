<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Resources;

use App\Domain\Restaurant\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Table
 */
class TableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'number' => $this->number,
            'capacity' => $this->capacity,
            'location' => $this->location,
            'is_available' => $this->is_available,
            'description' => $this->description,
            'min_order_amount' => $this->min_order_amount,
            'features' => $this->features,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
