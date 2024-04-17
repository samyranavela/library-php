<?php

namespace App\Lending\Patron\Application\Hold;

use App\Catalogue\BookId;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;

final readonly class CancelHoldCommand
{
    private function __construct(
        public CarbonImmutable $timestamp,
        public PatronId $patronId,
        public BookId $bookId,
    ) {
    }

    public static function create(CarbonImmutable $timestamp, PatronId $patronId, BookId $bookId): self
    {
        return new self($timestamp, $patronId, $bookId);
    }
}
