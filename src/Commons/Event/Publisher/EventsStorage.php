<?php

namespace App\Commons\Event\Publisher;

use App\Commons\Event\DomainEvent;
use Illuminate\Support\Collection;

interface EventsStorage
{
    public function save(DomainEvent $event): void;

    /**
     * @return Collection<DomainEvent>
     */
    public function toPublish(): Collection;

    /**
     * @param Collection<DomainEvent> $events
     */
    public function published(Collection $events): void;
}
