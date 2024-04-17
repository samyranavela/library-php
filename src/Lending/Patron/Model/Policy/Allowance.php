<?php

namespace App\Lending\Patron\Model\Policy;

final readonly class Allowance
{
    private function __construct()
    {
    }

    public static function allow(): self
    {
        return new self();
    }
}
