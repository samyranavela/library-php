<?php

namespace App\Lending\Book\Model;

use App\Catalogue\BookId;
use App\Catalogue\BookType;

final readonly class BookInformation
{
    private function __construct(
        public BookId $bookId,
        public BookType $bookType,
    ) {
    }

    public static function create(BookId $bookId, BookType $type): self
    {
        return new self($bookId, $type);
    }
}
