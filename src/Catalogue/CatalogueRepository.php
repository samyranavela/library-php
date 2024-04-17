<?php

namespace App\Catalogue;

use Munus\Control\Option;

interface CatalogueRepository
{
    public function saveNewBook(Book $book): Book;

    public function saveNewBookInstance(BookInstance $bookInstance): BookInstance;

    /**
     * @return Option<Book>
     */
    public function findBy(ISBN $isbn): Option;
}
