<?php

namespace App\Lending\Patron\Application\Hold;

use App\Catalogue\BookId;
use App\Lending\Book\Model\AvailableBook;
use Munus\Control\Option;

interface FindAvailableBook
{
    /**
     * @return Option<AvailableBook>
     */
    public function findAvailableBookBy(BookId $bookId): Option;
}
