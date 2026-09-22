<?php

namespace App\Support;

final class PhoneNumber
{
    public static function normalize(?string $phone): ?string
    {
        if (blank($phone)) return null;
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '00')) $digits = substr($digits, 2);
        if (str_starts_with($digits, '0')) $digits = '62' . substr($digits, 1);
        return str_starts_with($digits, '62') ? $digits : null;
    }
}
