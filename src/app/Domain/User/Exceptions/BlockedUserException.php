<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use Exception;

class BlockedUserException extends Exception
{
    public function __construct()
    {
        parent::__construct('User is blocked.', 403);
    }
}
