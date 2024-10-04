<?php

namespace App\Lending\PatronProfile\Model;

use Illuminate\Support\Collection;

final readonly class HoldsView
{
    private function __construct(
        public Collection $currentHolds,
    ) {
    }

    public static function create(Collection $currentHolds): self
    {
        return new self($currentHolds);
    }
}
