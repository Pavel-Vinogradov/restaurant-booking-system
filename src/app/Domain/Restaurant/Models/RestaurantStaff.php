<?php

namespace App\Domain\Restaurant\Models;

use App\Domain\Restaurant\Enums\StaffRole;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantStaff extends Model
{
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => StaffRole::class,
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
