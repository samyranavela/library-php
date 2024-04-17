<?php

namespace App\Lending\Patron\Model\Policy\Policy;

use App\Lending\Book\Model\AvailableBook;
use App\Lending\Patron\Model\HoldDuration;
use App\Lending\Patron\Model\OverdueCheckouts;
use App\Lending\Patron\Model\Patron;
use App\Lending\Patron\Model\Policy\Allowance;
use App\Lending\Patron\Model\Policy\PlacingOnHoldPolicy;
use App\Lending\Patron\Model\Policy\Rejection;
use Munus\Control\Either;

final readonly class OverdueCheckoutsRejectionPolicy implements PlacingOnHoldPolicy
{
    /**
     * @return Either<Allowance, Rejection>
     */
    public function __invoke(AvailableBook $toHold, Patron $patron, HoldDuration $holdDuration): Either
    {
        if ($patron->overdueCheckoutsAt($toHold->libraryBranch) >= OverdueCheckouts::MAX_COUNT_OF_OVERDUE_RESOURCES) {
            return Either::left(Rejection::withReason('Cannot place on hold when there are overdue checkouts.'));
        }

        return Either::right(Allowance::allow());
    }
}
