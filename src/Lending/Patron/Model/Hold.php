<?php

namespace App\Lending\Patron\Model;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;

final readonly class Hold
{
    private function __construct(
        public BookId $book,
        public LibraryBranchId $libraryBranchId,
    ) {
    }

    public static function create(BookId $bookId, LibraryBranchId $libraryBranchId): self
    {
        return new self($bookId, $libraryBranchId);
    }
}
