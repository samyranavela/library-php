<?php

namespace App\Lending\Book\Model;

use App\Catalogue\BookId;
use App\Catalogue\BookType;
use App\Commons\Aggregates\Version;

trait BookTrait
{
    public readonly BookInformation $bookInformation;
    public readonly Version $version;

    public function bookId(): BookId
    {
        return $this->bookInformation->bookId;
    }

    public function type(): BookType
    {
        return $this->bookInformation->bookType;
    }
}
