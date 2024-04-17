<?php

namespace App\Lending\Patron\Model\Event;

use App\Commons\Event\DomainEvent;
use App\Commons\Event\DomainEventTrait;
use Carbon\CarbonImmutable;
use Munus\Collection\GenericList;
use Munus\Control\Option;
use Symfony\Component\Uid\Uuid;

final readonly class BookPlacedOnHoldEvents implements PatronEvent
{
    use DomainEventTrait, PatronEventTrait;

    /**
     * @param Option<MaximumNumberOhHoldsReached> $maximumNumberOhHoldsReached
     */
    private function __construct(
        protected Uuid $eventId,
        protected Uuid $aggregateId,
        protected CarbonImmutable $when,
        protected Uuid $patronId,
        public BookPlacedOnHold $bookPlacedOnHold,
        public Option $maximumNumberOhHoldsReached,
    ) {
    }

    public static function events(
        BookPlacedOnHold $bookPlacedOnHold,
        ?MaximumNumberOhHoldsReached $maximumNumberOhHoldsReached = null,
    ): self {
        return new self(
            Uuid::v7(),
            $bookPlacedOnHold->patronId(),
            CarbonImmutable::now(),
            $bookPlacedOnHold->patronId(),
            $bookPlacedOnHold,
            $maximumNumberOhHoldsReached ? Option::of($maximumNumberOhHoldsReached) : Option::none(),
        );
    }

    public function when(): CarbonImmutable
    {
        return $this->bookPlacedOnHold->when();
    }

    /**
     * @return GenericList<DomainEvent>
     */
    public function normalize(): GenericList
    {
        return GenericList::of($this->bookPlacedOnHold)
            ->appendAll($this->maximumNumberOhHoldsReached->toStream())
        ;
    }
}
