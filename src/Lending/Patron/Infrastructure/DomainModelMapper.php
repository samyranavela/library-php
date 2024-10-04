<?php

namespace App\Lending\Patron\Infrastructure;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\Patron;
use App\Lending\Patron\Model\PatronFactory;
use App\Lending\Patron\Model\PatronId;
use Illuminate\Support\Collection;
use Munus\Collection\Map;
use Munus\Collection\Set;
use Munus\Tuple;

final readonly class DomainModelMapper
{
    public function __construct(
        private PatronFactory $patronFactory,
    ) {
    }

    public function map(PatronEntity $entity): Patron
    {
        return $this->patronFactory->create(
            $entity->patronType,
            PatronId::from($entity->patronId),
            $this->mapPatronOverdueCheckouts($entity),
            $this->mapPatronHolds($entity),
        );
    }

    /**
     * @return Map<string, Set<BookId>>
     */
    private function mapPatronOverdueCheckouts(PatronEntity $entity): Map
    {
        return Map::fromArray(
            Collection::make($entity->checkouts)
                ->groupBy(static fn (OverdueCheckoutEntity $entity): string => $entity->libraryBranchId->toRfc4122())
                ->map(static fn (array $checkouts): Set => Set::ofAll($checkouts)
                    ->map(static fn (OverdueCheckoutEntity $entity): BookId => BookId::from($entity->bookId))
                )
                ->toArray()
        );
    }

    /**
     * @return Set<Tuple<BookId, LibraryBranchId>>
     */
    private function mapPatronHolds(PatronEntity $entity): Set
    {
        return Set::ofAll(
            $entity->booksOnHold
                ->map(static fn (HoldEntity $hold): Tuple => Tuple::of(BookId::from($hold->bookId), LibraryBranchId::from($hold->libraryBranchId)))
                ->toArray()
        );
    }
}
