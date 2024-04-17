<?php

namespace App\Lending\DailySheet\Model;

use App\Lending\Patron\Model\Event\BookHoldExpired;
use Illuminate\Support\Collection;
use Munus\Collection\Stream;

final readonly class HoldsToExpireSheet
{
    /**
     * @param Collection<ExpiredHold> $expiredHolds
     */
    private function __construct(
        private Collection $expiredHolds,
    ) {
        $this->expiredHolds
            ->ensure(ExpiredHold::class)
        ;
    }

    public static function create(Collection $expiredHolds): self
    {
        return new self($expiredHolds);
    }

    /**
     * @return Stream<BookHoldExpired>
     */
    public function toStreamOfEvents(): Stream
    {
        return Stream::ofAll(
            $this->expiredHolds
                ->map(static fn (ExpiredHold $expiredHold): BookHoldExpired => $expiredHold->toEvent())
        );
    }

    public function count(): int
    {
        return $this->expiredHolds->count();
    }
}
