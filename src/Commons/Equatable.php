<?php

namespace App\Commons;

/**
 * @template T of object
 */
interface Equatable
{
    /**
     * @param Equatable<T> $other
     */
    public function equals(Equatable $other): bool;
}
