<?php

namespace App\Commons\Event;

use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

trait DomainEventTrait
{
    private function __construct(
        protected readonly Uuid $eventId,
        protected readonly Uuid $aggregateId,
        protected readonly CarbonImmutable $when,
    ) {
    }

    public function eventId(): Uuid
    {
        return $this->eventId;
    }

    public function aggregateId(): Uuid
    {
        return $this->aggregateId;
    }

    public function when(): CarbonImmutable
    {
        return $this->when;
    }
}
