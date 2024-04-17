<?php

namespace App\Lending\Book\Model;

use App\Commons\Aggregates\Version;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\Event\BookCheckedOut;
use App\Lending\Patron\Model\Event\BookHoldCanceled;
use App\Lending\Patron\Model\Event\BookHoldExpired;
use App\Lending\Patron\Model\Event\BookReturned;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;

final readonly class BookOnHold implements Book
{
    use BookTrait;

    private function __construct(
        public BookInformation $bookInformation,
        public LibraryBranchId $holdPlacedAt,
        public PatronId $byPatron,
        public CarbonImmutable $holdTill,
        public Version $version,
    ) {
    }

    public static function create(
        BookInformation $bookInformation,
        LibraryBranchId $holdPlacedAt,
        PatronId $byPatron,
        ?CarbonImmutable $holdTill,
        Version $version,
    ): self
    {
        return new self($bookInformation, $holdPlacedAt, $byPatron, $holdTill, $version);
    }

    public function return(BookReturned $bookReturned): AvailableBook
    {
        return AvailableBook::create(
            $this->bookInformation,
            LibraryBranchId::from($bookReturned->libraryBranchId),
            $this->version,
        );
    }

    public function expire(BookHoldExpired $bookHoldExpired): AvailableBook
    {
        return AvailableBook::create(
            $this->bookInformation,
            LibraryBranchId::from($bookHoldExpired->libraryBranchId),
            $this->version,
        );
    }

    public function checkout(BookCheckedOut $bookHoldExpired): CheckedOutBook
    {
        return CheckedOutBook::create(
            $this->bookInformation,
            LibraryBranchId::from($bookHoldExpired->libraryBranchId),
            PatronId::from($bookHoldExpired->aggregateId()),
            $this->version
        );
    }

    public function cancel(BookHoldCanceled $bookHoldCanceled): AvailableBook
    {
        return AvailableBook::create(
            $this->bookInformation,
            LibraryBranchId::from($bookHoldCanceled->libraryBranchId),
            $this->version,
        );
    }

    public function by(PatronId $patronId): bool
    {
        return $this->byPatron->equals($patronId);
    }
}
