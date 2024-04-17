<?php

namespace App\Lending\DailySheet\Model;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\Event\BookHoldExpired;
use App\Lending\Patron\Model\PatronId;

final readonly class ExpiredHold
{
    public function __construct(
        public BookId $heldBook,
        public PatronId $patron,
        public LibraryBranchId $library,
    ) {
    }

    public function toEvent(): BookHoldExpired
    {
        return BookHoldExpired::now(
            $this->heldBook,
            $this->patron,
            $this->library
        );
    }
}
