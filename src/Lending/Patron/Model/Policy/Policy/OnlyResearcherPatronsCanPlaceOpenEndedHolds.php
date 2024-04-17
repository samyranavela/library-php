<?php

namespace App\Lending\Patron\Model\Policy\Policy;

use App\Lending\Book\Model\AvailableBook;
use App\Lending\Patron\Model\HoldDuration;
use App\Lending\Patron\Model\Patron;
use App\Lending\Patron\Model\Policy\Allowance;
use App\Lending\Patron\Model\Policy\PlacingOnHoldPolicy;
use App\Lending\Patron\Model\Policy\Rejection;
use Munus\Control\Either;

final readonly class OnlyResearcherPatronsCanPlaceOpenEndedHolds implements PlacingOnHoldPolicy
{
    /**
     * @return Either<Allowance, Rejection>
     */
    public function __invoke(AvailableBook $toHold, Patron $patron, HoldDuration $holdDuration): Either
    {
        if ($patron->isRegular() && $holdDuration->isOpenEnded()) {
            return Either::left(Rejection::withReason('Regular patron cannot place open ended holds.'));
        }

        return Either::right(Allowance::allow());
    }
}
