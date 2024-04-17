<?php

namespace App\Commons\Event\Publisher;

use App\Commons\Event\DomainEvent;
use App\Commons\Event\DomainEvents;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(
    frequency: '5 seconds',
    method: 'publishAllPeriodically',
)]
final readonly class StoreAndForwardDomainEventPublisher implements DomainEvents
{
    public function __construct(
        private DomainEvents $eventPublisher,
        private EventsStorage $eventsStorage,
    ) {
    }

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->eventsStorage->save($event);
        }
    }

    public function publishAllPeriodically(): void
    {
        $events = $this->eventsStorage->toPublish();
        $this->eventPublisher->publish(...$events);
        $this->eventsStorage->published($events);
    }
}
