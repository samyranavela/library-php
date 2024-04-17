<?php

namespace App\Lending\Patron\Model\Policy;

use App\Lending\Book\Model\AvailableBook;
use App\Lending\Patron\Model\HoldDuration;
use App\Lending\Patron\Model\Patron;
use Munus\Control\Either;

interface PlacingOnHoldPolicy
{
    /**
     * @return Either<Allowance, Rejection>
     */
    public function __invoke(AvailableBook $toHold, Patron $patron, HoldDuration $holdDuration): Either;
}
