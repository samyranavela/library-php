<?php

namespace App\Lending\Patron\Model;

use Munus\Control\Option;

interface Patrons
{
    /**
     * @return Option<Patron>
     */
    public function findBy(PatronId $patronId): Option;
}
