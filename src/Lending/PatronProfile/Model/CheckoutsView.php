<?php

namespace App\Lending\PatronProfile\Model;

use Illuminate\Support\Collection;

final readonly class CheckoutsView
{
    public function __construct(
        public Collection $currentCheckouts,
    ) {
    }
}
