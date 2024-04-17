<?php

namespace App\Lending\Patron\Model;

use App\Lending\Book\Model\BookOnHold;
use Munus\Collection\Set;

final readonly class PatronHolds
{
    public const int MAX_NUMBER_OF_HOLDS = 5;

    /**
     * @param Set<Hold> $resourcesOnHold
     */
    private function __construct(
        public Set $resourcesOnHold,
    ) {
    }

    public static function create(Set $resourcesOnHold): self
    {
        return new self($resourcesOnHold);
    }

    public function a(BookOnHold $bookOnHold): bool
    {
        $hold = Hold::create($bookOnHold->bookId(), $bookOnHold->holdPlacedAt);

        return $this->resourcesOnHold->contains($hold);
    }

    public function count(): int
    {
        return $this->resourcesOnHold->length();
    }

    public function maximumHoldsAfterHolding(): bool
    {
        return $this->count() + 1 === self::MAX_NUMBER_OF_HOLDS;
    }
}
