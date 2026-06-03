<?php

namespace App\Domain\Restaurant\Models;

use App\Domain\Restaurant\Enums\RestaurantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'timezone',
        'status',
    ];

    protected $casts = [
        'status' => RestaurantStatus::class,
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(RestaurantStaff::class);
    }
}
