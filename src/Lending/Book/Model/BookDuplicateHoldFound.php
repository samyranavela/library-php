<?php

namespace App\Lending\Book\Model;

use App\Catalogue\BookId;
use App\Commons\Event\DomainEvent;
use App\Commons\Event\DomainEventTrait;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

final readonly class BookDuplicateHoldFound implements DomainEvent
{
    use DomainEventTrait;

    private function __construct(
        protected Uuid $eventId,
        protected Uuid $aggregateId,
        protected CarbonImmutable $when,
        public Uuid $bookId,
        public Uuid $firstPatronId,
        public Uuid $secondPatronId,
        public Uuid $libraryBranchId,
    ) {
    }

    public static function now(
        BookId $bookId,
        PatronId $firstPatronId,
        PatronId $secondPatronId,
        LibraryBranchId $libraryBranchId,
    ): self {
        return new self(
            Uuid::v7(),
            $bookId->bookId,
            CarbonImmutable::now(),
            $bookId->bookId,
            $firstPatronId->patronId,
            $secondPatronId->patronId,
            $libraryBranchId->libraryBranchId,
        );
    }
}
