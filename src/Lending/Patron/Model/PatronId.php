<?php

namespace App\Lending\Patron\Model;

use App\Commons\Equatable;
use Symfony\Component\Uid\Uuid;

/**
 * @implements Equatable<PatronId>
 */
final readonly class PatronId implements Equatable
{
    private function __construct(
        public Uuid $patronId,
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v7());
    }

    public static function from(Uuid $patronId): self
    {
        return new self($patronId);
    }

    /**
     * @param Equatable<PatronId> $other
     */
    public function equals(Equatable $other): bool
    {
        return $other instanceof self && $this->patronId->equals($other->patronId);
    }
}
