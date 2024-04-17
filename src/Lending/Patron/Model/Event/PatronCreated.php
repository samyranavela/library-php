<?php

namespace App\Lending\Patron\Model\Event;

use App\Commons\Event\DomainEventTrait;
use App\Lending\Patron\Model\PatronId;
use App\Lending\Patron\Model\PatronType;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

final readonly class PatronCreated implements PatronEvent
{
    use DomainEventTrait, PatronEventTrait;

    private function __construct(
        protected Uuid $eventId,
        protected Uuid $aggregateId,
        protected CarbonImmutable $when,
        public Uuid $patronId,
        public PatronType $patronType,
    ) {
    }

    public static function now(
        PatronId $patronId,
        PatronType $patronType,
    ): self {
        return new self(
            Uuid::v7(),
            $patronId->patronId,
            CarbonImmutable::now(),
            $patronId->patronId,
            $patronType,
        );
    }
}
