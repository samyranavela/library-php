<?php

namespace App\Lending\PatronProfile\Model;

use App\Catalogue\BookId;
use Carbon\CarbonImmutable;

final readonly class Hold
{
    private function __construct(
        public BookId $book,
        public CarbonImmutable $till,
    ) {
    }

    public static function create(BookId $bookId, CarbonImmutable $till): self
    {
        return new self($bookId, $till);
    }
}
