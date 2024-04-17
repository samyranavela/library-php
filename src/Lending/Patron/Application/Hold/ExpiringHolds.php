<?php

namespace App\Lending\Patron\Application\Hold;

use App\Commons\Command\BatchResult;
use App\Lending\DailySheet\Model\DailySheet;
use App\Lending\Patron\Model\Event\BookHoldExpired;
use App\Lending\Patron\Model\Patrons;
use Munus\Control\TryTo;

final readonly class ExpiringHolds
{
    public function __construct(
        private DailySheet $dailySheet,
        private Patrons $patronRepository,
    ) {
    }

    /**
     * @return TryTo<BatchResult>
     */
    public function registerOverdueCheckouts(): TryTo
    {
        return TryTo::run(static fn () => $this->dailySheet
            ->queryForHoldsToExpireSheet()
            ->toStreamOfEvents()
            ->map($this->publish(...))
            ->find(static fn (TryTo $try): bool => $try->isFailure())
            ->map(static fn (): BatchResult => BatchResult::SomeFailed)
            ->getOrElse(BatchResult::FullSuccess)
        );
    }

    private function publish(BookHoldExpired $event): TryTo
    {
        return TryTo::run(static fn () => $this->patronRepository->publish($event));
    }
}
