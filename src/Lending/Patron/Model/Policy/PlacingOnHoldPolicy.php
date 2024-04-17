<?php

namespace App\Lending\Patron\Model\Policy;

use App\Lending\Book\Model\AvailableBook;
use App\Lending\Patron\Model\HoldDuration;
use App\Lending\Patron\Model\Patron;
use Munus\Control\Either;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'placing_on_hold_policy')]
interface PlacingOnHoldPolicy
{
    /**
     * @return Either<Allowance, Rejection>
     */
    public function __invoke(AvailableBook $toHold, Patron $patron, HoldDuration $holdDuration): Either;
}
