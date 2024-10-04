<?php

namespace App\Lending\Patron\Model;

final readonly class PatronInformation
{
    private function __construct(
        public PatronId $patronId,
        public PatronType $type,
    ) {
    }

    public static function create(PatronType $patronType, PatronId $patronId): self
    {
        return new self($patronId, $patronType);
    }

    public function isRegular(): bool
    {
        return $this->type->equals(PatronType::Regular);
    }
}
