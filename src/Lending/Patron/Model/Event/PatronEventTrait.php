<?php

namespace App\Lending\Patron\Model\Event;

use Munus\Collection\GenericList;
use Symfony\Component\Uid\Uuid;

trait PatronEventTrait
{
    public function __construct(
        protected readonly Uuid $patronId,
    ) {
    }

    /**
     * @return GenericList<PatronEvent>
     */
    public function normalize(): GenericList
    {
        return GenericList::of($this);
    }

    public function patronId(): Uuid
    {
        return $this->patronId;
    }
}
