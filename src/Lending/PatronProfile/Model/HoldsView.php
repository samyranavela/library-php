<?php

namespace App\Lending\PatronProfile\Model;

use Illuminate\Support\Collection;

final readonly class HoldsView
{
    public function __construct(
        public Collection $currentHolds,
    ) {
    }
}
