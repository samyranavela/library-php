<?php

namespace App\Lending\PatronProfile\Model;

use App\Lending\Patron\Model\PatronId;

interface PatronProfiles
{
    public function fetchFor(PatronId $patronId): PatronProfile;
}
