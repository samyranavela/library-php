<?php

namespace App\Lending\Patron\Model\Event;

use App\Catalogue\BookId;
use App\Commons\Event\DomainEventTrait;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

final readonly class BookHoldCancelingFailed implements PatronEvent
{
    use DomainEventTrait, PatronEventTrait;

    private function __construct(
        protected Uuid $eventId,
        protected Uuid $aggregateId,
        protected CarbonImmutable $when,
        protected Uuid $patronId,
        public Uuid $bookId,
        public Uuid $libraryBranchId,
    ) {
    }

    public static function now(
        BookId $bookId,
        PatronId $patronId,
        LibraryBranchId $libraryBranchId,
    ): self {
        return new self(
            Uuid::v7(),
            $patronId->patronId,
            CarbonImmutable::now(),
            $patronId->patronId,
            $bookId->bookId,
            $libraryBranchId->libraryBranchId,
        );
    }
}
