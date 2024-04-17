<?php

namespace App\Lending\Patron\Model;

final readonly class PatronInformation
{
    public function __construct(
        public PatronId $patronId,
        public PatronType $type,
    ) {
    }

    public function isRegular(): bool
    {
        return $this->type->equals(PatronType::Regular);
    }
}
