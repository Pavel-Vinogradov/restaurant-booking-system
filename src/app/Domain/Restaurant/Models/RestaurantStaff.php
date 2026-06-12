<?php

namespace App\Domain\Restaurant\Models;

use App\Domain\Restaurant\Enums\StaffRole;
use App\Domain\User\Models\User;
use Database\Factories\RestaurantStaffFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(RestaurantStaffFactory::class)]
class RestaurantStaff extends Model
{
    use HasFactory;

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
