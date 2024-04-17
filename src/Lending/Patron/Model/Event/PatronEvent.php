<?php

namespace App\Lending\Patron\Model\Event;

use App\Commons\Event\DomainEvent;
use Munus\Collection\GenericList;
use Symfony\Component\Uid\Uuid;

interface PatronEvent extends DomainEvent
{
    /**
     * @return GenericList<PatronEvent>
     */
    public function normalize(): GenericList;

    public function patronId(): Uuid;
}
