<?php

namespace App\Lending\Patron\Model;

use App\Lending\Book\Model\AvailableBook;
use App\Lending\Book\Model\BookOnHold;
use Illuminate\Support\Collection;

final readonly class PatronHolds
{
    public const int MAX_NUMBER_OF_HOLDS = 5;

    /**
     * @param Collection<Hold> $resourcesOnHold
     */
    public function __construct(
        public Collection $resourcesOnHold,
    ) {
    }

    public function a(BookOnHold $bookOnHold): bool
    {
        $hold = Hold::create($bookOnHold->bookId(), $bookOnHold->holdPlacedAt);

        return $this->resourcesOnHold->contains($hold);
    }

    public function count(): int
    {
        return $this->resourcesOnHold->count();
    }

    public function maximumHoldsAfterHolding(): bool
    {
        return $this->count() + 1 === self::MAX_NUMBER_OF_HOLDS;
    }
}
