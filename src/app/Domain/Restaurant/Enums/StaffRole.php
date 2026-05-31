<?php

namespace App\Domain\Restaurant\Enums;

enum StaffRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Hostess = 'hostess';
}
