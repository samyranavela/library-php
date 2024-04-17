<?php

namespace App\Lending\Book\Model;

use App\Catalogue\BookId;
use Munus\Control\Option;

interface BookRepository
{
    /**
     * @return Option<Book>
     */
    public function findBy(BookId $bookId): Option;

    public function save(Book $book): void;
}
