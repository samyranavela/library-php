<?php

namespace App\Lending\PatronProfile\Model;

use App\Catalogue\BookId;
use Carbon\CarbonImmutable;

final readonly class Checkout
{
    private function __construct(
        public BookId $bookId,
        public CarbonImmutable $till,
    ) {
    }

    public static function create(BookId $bookId, CarbonImmutable $till): self
    {
        return new self($bookId, $till);
    }
}
