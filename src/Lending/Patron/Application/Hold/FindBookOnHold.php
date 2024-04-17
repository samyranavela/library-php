<?php

namespace App\Lending\Patron\Application\Hold;

use App\Catalogue\BookId;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Patron\Model\PatronId;
use Munus\Control\Option;

interface FindBookOnHold
{
    /**
     * @return Option<BookOnHold>
     */
    public function findBookOnHold(BookId $bookId, PatronId $patronId): Option;
}
