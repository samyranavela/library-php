<?php

namespace App\Lending\PatronProfile\Model;

use App\Catalogue\BookId;
use Carbon\CarbonImmutable;

final readonly class Checkout
{
    public function __construct(
        public BookId $bookId,
        public CarbonImmutable $till,
    ) {
    }
}
