<?php

namespace App\Catalogue;

use App\Catalogue\Book\Book;

final readonly class BookInstance
{
    private function __construct(
        public ISBN $isbn,
        public BookId $bookId,
        public BookType $bookType,
    ) {
    }

    public static function instanceOf(Book $book, BookType $bookType): self
    {
        return new self($book->isbn, BookId::generate(), $bookType);
    }
}
