<?php

namespace App\Lending\Book\Model;

use App\Commons\Aggregates\Version;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\Event\BookReturned;
use App\Lending\Patron\Model\PatronId;

final readonly class CheckedOutBook implements Book
{
    use BookTrait;

    private function __construct(
        public BookInformation $bookInformation,
        public LibraryBranchId $checkedOutAt,
        public PatronId $byPatron,
        public Version $version,
    ) {
    }

    public function handle(BookReturned $bookReturned): AvailableBook
    {
        return AvailableBook::create(
            $this->bookInformation,
            LibraryBranchId::from($bookReturned->libraryBranchId),
            $this->version,
        );
    }

    public static function create(BookInformation $bookInformation, LibraryBranchId $libraryBranch, PatronId $patronId, Version $version): self
    {
        return new self($bookInformation, $libraryBranch, $patronId, $version);
    }
}
