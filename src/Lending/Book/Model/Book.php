<?php

namespace App\Lending\Book\Model;

use App\Catalogue\BookId;
use App\Catalogue\BookType;

interface Book
{
    public function bookId(): BookId;

    public function type(): BookType;
}
