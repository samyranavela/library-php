<?php

namespace App\Commons\Event\Publisher;

use App\Commons\Event\DomainEvent;
use Illuminate\Support\Collection;

final readonly class InMemoryEventsStorage implements EventsStorage
{
    private \SplObjectStorage $eventList;

    public function __construct()
    {
        $this->eventList = new \SplObjectStorage();
    }

    public function save(DomainEvent $event): void
    {
        $this->eventList->attach($event);
    }

    public function toPublish(): Collection
    {
        return Collection::make($this->eventList);
    }

    public function published(Collection $events): void
    {
        $events->each(static fn(DomainEvent $event) => $this->eventList->detach($events));
    }
}
