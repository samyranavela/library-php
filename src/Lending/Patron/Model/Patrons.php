<?php

namespace App\Lending\Patron\Model;

use App\Lending\Patron\Model\Event\PatronEvent;
use Munus\Control\Option;

interface Patrons
{
    /**
     * @return Option<Patron>
     */
    public function findBy(PatronId $patronId): Option;

    public function publish(PatronEvent $event): Patron;
}
