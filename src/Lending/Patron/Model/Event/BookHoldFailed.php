<?php

namespace App\Lending\Patron\Model\Event;

use App\Catalogue\BookId;
use App\Commons\Event\DomainEventTrait;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\PatronInformation;
use App\Lending\Patron\Model\Policy\Rejection;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

final readonly class BookHoldFailed implements PatronEvent
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
        LibraryBranchId $libraryBranchId,
        PatronInformation $patronInformation,
    ): self {
        return new self(
            Uuid::v7(),
            $patronInformation->patronId->patronId,
            CarbonImmutable::now(),
            $patronInformation->patronId->patronId,
            $rejection->reason->reason,
            $bookId->bookId,
            $libraryBranchId->libraryBranchId,
        );
    }
}
