<?php

namespace App\Lending\Patron\Application\Checkout;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\CheckoutDuration;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;

final readonly class CheckOutBookCommand
{
    private function __construct(
        public CarbonImmutable $timestamp,
        public PatronId $patronId,
        public LibraryBranchId $libraryBranchId,
        public BookId $bookId,
        public int $noOfDays,
    ) {
    }

    public static function create(
        PatronId $patronId,
        LibraryBranchId $libraryBranchId,
        BookId $bookId,
        int $noOfDays
    ): self {
        return new self(CarbonImmutable::now(), $patronId, $libraryBranchId, $bookId, $noOfDays);
    }

    public function getCheckoutDuration(): CheckoutDuration
    {
        return CheckoutDuration::forNumberOfDays($this->noOfDays);
    }
}
