<?php

namespace App\Lending\Patron\Model\Event;

use App\Commons\Event\DomainEvent;
use Illuminate\Support\Collection;

interface PatronEvent extends DomainEvent
{
    /**
     * @return Collection<PatronEvent>
     */
    public function normalize(): Collection;
}
