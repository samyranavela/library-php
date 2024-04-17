<?php

namespace App\Lending\DailySheet\Model;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\Event\OverdueCheckoutRegistered;
use App\Lending\Patron\Model\PatronId;

final readonly class OverdueCheckout
{
    private function __construct(
        public BookId $checkedOutBook,
        public PatronId $patron,
        public LibraryBranchId $library,
    ) {
    }

    public static function create(BookId $checkedOutBook, PatronId $patron, LibraryBranchId $library): self
    {
        return new self($checkedOutBook, $patron, $library);
    }

    public function toEvent(): OverdueCheckoutRegistered
    {
        return OverdueCheckoutRegistered::now(
            $this->checkedOutBook,
            $this->patron,
            $this->library
        );
    }
}
