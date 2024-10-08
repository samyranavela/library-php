<?php

namespace App\Commons\Event\Publisher;

use App\Commons\Event\DomainEvent;
use App\Commons\Event\DomainEvents;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(
    frequency: '5 seconds',
    method: 'publishAllPeriodically',
)]
#[AsDecorator(decorates: JustForwardDomainEventPublisher::class)]
final readonly class StoreAndForwardDomainEventPublisher implements DomainEvents
{
    public function __construct(
        private EventsStorage $eventsStorage,
        private DomainEvents $inner,
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
        $events->each([$this->inner, 'publish']);
        $this->eventsStorage->published($events);
    }
}
