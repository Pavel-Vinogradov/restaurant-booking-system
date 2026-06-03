<?php

namespace App\Domain\Restaurant\Enums;

enum RestaurantStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
