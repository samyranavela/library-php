<?php

namespace App\Lending\PatronProfile\Model;


use App\Catalogue\BookId;
use Munus\Control\Option;

final readonly class PatronProfile
{
    public function __construct(
        public HoldsView $holdsView,
        public CheckoutsView $currentCheckouts,
    ) {
    }

    /**
     * @return Option<Hold>
     */
    public function findHold(BookId $bookId): Option
    {
        return Option::of(
            $this->holdsView
                ->currentHolds
                ->first(
                    static fn (Hold $hold) => $hold->book->bookId->equals($bookId)
                )
        );
    }

    /**
     * @return Option<Checkout>
     */
    public function findCheckout(BookId $bookId): Option
    {
        return Option::of(
            $this->currentCheckouts
                ->currentCheckouts
                ->first(
                    static fn (Hold $hold) => $hold->book->bookId->equals($bookId)
                )
        );
    }
}
