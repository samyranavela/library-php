<?php

namespace App\Lending\Book\Model;

use App\Catalogue\BookType;
use App\Commons\Aggregates\Version;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\Event\BookPlacedOnHold;
use App\Lending\Patron\Model\PatronId;

final readonly class AvailableBook implements Book
{
    use BookTrait;

    private function __construct(
        public BookInformation $bookInformation,
        public LibraryBranchId $libraryBranch,
        public Version $version,
    ) {
    }

    public static function create(BookInformation $bookInformation, LibraryBranchId $libraryBranch, Version $version): self
    {
        return new self($bookInformation, $libraryBranch, $version);
    }

    public function isRestricted(): bool
    {
        return $this->bookInformation->bookType->equals(BookType::Restricted);
    }

    public function handle(BookPlacedOnHold $bookPlaceOnHold): BookOnHold
    {
        return BookOnHold::create(
            $this->bookInformation,
            LibraryBranchId::from($bookPlaceOnHold->libraryBranchId),
            PatronId::from($bookPlaceOnHold->aggregateId()),
            $bookPlaceOnHold->holdTill,
            $this->version
        );
    }
}
