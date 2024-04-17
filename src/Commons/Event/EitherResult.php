<?php

namespace App\Commons\Event;

use Munus\Control\Either;

final readonly class EitherResult
{
    private function __construct()
    {
    }

    public static function announceFailure(mixed $left): Either
    {
        return Either::left($left);
    }

    public static function announceSuccess(mixed $right): Either
    {
        return Either::right($right);
    }
}
