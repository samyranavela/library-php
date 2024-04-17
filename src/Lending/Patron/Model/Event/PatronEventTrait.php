<?php

namespace App\Lending\Patron\Model\Event;

use Illuminate\Support\Collection;

trait PatronEventTrait
{
    /**
     * @return Collection<PatronEvent>
     */
    public function normalize(): Collection
    {
        return Collection::make($this);
    }
}
