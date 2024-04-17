<?php

namespace App\Lending\PatronProfile\Model;

use Illuminate\Support\Collection;

final readonly class CheckoutsView
{
    private function __construct(
        public Collection $currentCheckouts,
    ) {
    }

    public static function create(Collection $currentCheckouts): self
    {
        return new self($currentCheckouts);
    }
}
