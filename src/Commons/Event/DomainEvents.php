<?php

namespace App\Commons\Event;

interface DomainEvents
{
    public function publish(DomainEvent ...$events): void;
}
