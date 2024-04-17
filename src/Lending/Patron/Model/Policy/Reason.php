<?php

namespace App\Lending\Patron\Model\Policy;

final readonly class Reason
{
    private function __construct(
        public string $reason,
    ) {
    }

    public static function create(string $reason): self
    {
        return new self($reason);
    }
}
