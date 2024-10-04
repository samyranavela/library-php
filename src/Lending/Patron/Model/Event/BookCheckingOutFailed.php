<?php

namespace App\Lending\Patron\Model\Event;

use App\Catalogue\BookId;
use App\Commons\Event\DomainEventTrait;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\PatronId;
use App\Lending\Patron\Model\Policy\Rejection;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

final readonly class BookCheckingOutFailed implements PatronEvent
{
    use DomainEventTrait, PatronEventTrait;

    private function __construct(
        protected Uuid $eventId,
        protected Uuid $aggregateId,
        protected CarbonImmutable $when,
        protected Uuid $patronId,
        public string $reason,
        public Uuid $bookId,
        public Uuid $libraryBranchId,
    ) {
    }

    public static function now(
        Rejection $rejection,
        BookId $bookId,
        PatronId $patronId,
        LibraryBranchId $libraryBranchId,
    ): self
    {
        return new self(
            Uuid::v7(),
            $patronId->patronId,
            CarbonImmutable::now(),
            $patronId->patronId,
            $rejection->reason->reason,
            $bookId->bookId,
            $libraryBranchId->libraryBranchId,
        );
    }
}
