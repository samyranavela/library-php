<?php

namespace App\Lending\Patron\Model\Event;

use App\Catalogue\BookId;
use App\Catalogue\BookType;
use App\Commons\Event\DomainEventTrait;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\HoldDuration;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

final readonly class BookPlacedOnHold implements PatronEvent
{
    use DomainEventTrait, PatronEventTrait;

    private function __construct(
        protected Uuid $eventId,
        protected Uuid $aggregateId,
        protected CarbonImmutable $when,
        public Uuid $bookId,
        public Uuid $patronId,
        public BookType $bookType,
        public Uuid $libraryBranchId,
        public CarbonImmutable $holdFrom,
        public ?CarbonImmutable $holdTill,
    ) {
    }

    public static function now(
        BookId $bookId,
        BookType $bookType,
        PatronId $patronId,
        LibraryBranchId $libraryBranchId,
        HoldDuration $holdDuration
    ): self
    {
        return new self(
            Uuid::v7(),
            $patronId->patronId,
            CarbonImmutable::now(),
            $bookId->bookId,
            $patronId->patronId,
            $bookType,
            $libraryBranchId->libraryBranchId,
            $holdDuration->from,
            $holdDuration->to
        );
    }
}
