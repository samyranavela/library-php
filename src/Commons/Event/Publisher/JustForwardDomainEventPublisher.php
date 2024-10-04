<?php

namespace App\Commons\Event\Publisher;

use App\Commons\Event\DomainEvent;
use App\Commons\Event\DomainEvents;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: DomainEvents::class)]
final readonly class JustForwardDomainEventPublisher implements DomainEvents
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
