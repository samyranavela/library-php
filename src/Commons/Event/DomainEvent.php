<?php

namespace App\Commons\Event;

use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

interface DomainEvent
{
    public function eventId(): Uuid;

    public function aggregateId(): Uuid;

    public function when(): CarbonImmutable;
}
