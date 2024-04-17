<?php

namespace App\Commons;

trait EquatableEnumTrait
{
    public function equals(Equatable $other): bool
    {
        return $this === $other;
    }
}
