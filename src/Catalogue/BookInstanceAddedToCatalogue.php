<?php

namespace App\Catalogue;

use App\Commons\Event\DomainEvent;
use App\Commons\Event\DomainEventTrait;
use Carbon\CarbonImmutable;
use Symfony\Component\Uid\Uuid;

final readonly class BookInstanceAddedToCatalogue implements DomainEvent
{
    use DomainEventTrait;

    private function __construct(
        protected Uuid $eventId,
        protected Uuid $aggregateId,
        protected CarbonImmutable $when,
        public string $isbn,
        public BookId $bookId,
        public BookType $type,
    ) {
    }

    public static function now(BookInstance $bookInstance): self
    {
        return new self(
            Uuid::v7(),
            $bookInstance->bookId->bookId,
            CarbonImmutable::now(),
            $bookInstance->isbn->isbn,
            $bookInstance->bookId,
            $bookInstance->bookType,
        );
    }
}
