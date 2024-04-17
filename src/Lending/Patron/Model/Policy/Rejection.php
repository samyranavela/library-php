<?php

namespace App\Lending\Patron\Model\Policy;

final readonly class Rejection
{
    private function __construct(
        public Reason $reason
    ) {
    }

    public static function withReason(string $reason): self
    {
        return new self(Reason::create($reason));
    }
}
