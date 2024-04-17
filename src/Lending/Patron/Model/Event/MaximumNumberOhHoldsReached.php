<?php

namespace App\Lending\Patron\Model\Event;

use App\Commons\Event\DomainEventTrait;
use App\Lending\Patron\Model\PatronInformation;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

final readonly class MaximumNumberOhHoldsReached implements PatronEvent
{
    use DomainEventTrait, PatronEventTrait;

    private function __construct(
        protected Uuid $eventId,
        protected Uuid $aggregateId,
        protected CarbonImmutable $when,
        public Uuid $patronId,
        public int $numberOfHolds,
    ) {
    }

    public static function now(
        PatronInformation $patronInformation,
        int $numberOfHolds,
    ): self {
        return new self(
            Uuid::v7(),
            $patronInformation->patronId->patronId,
            CarbonImmutable::now(),
            $patronInformation->patronId->patronId,
            $numberOfHolds,
        );
    }
}
