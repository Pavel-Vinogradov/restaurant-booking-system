<?php

declare(strict_types=1);

namespace App\Core\Support;

final class PhoneNormalizer
{
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }

        if (strlen($digits) === 11 && $digits[0] === '7') {
            $digits = '+' . $digits;
        }

        return $digits;
    }
}
