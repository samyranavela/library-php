<?php

namespace App\Lending\DailySheet\Model;

use App\Lending\Patron\Model\Event\BookCheckedOut;
use App\Lending\Patron\Model\Event\BookHoldCanceled;
use App\Lending\Patron\Model\Event\BookHoldExpired;
use App\Lending\Patron\Model\Event\BookPlacedOnHold;
use App\Lending\Patron\Model\Event\BookReturned;

interface DailySheet
{
    public function queryForCheckoutsToOverdue(): CheckoutsToOverdueSheet;

    public function queryForHoldsToExpireSheet(): HoldsToExpireSheet;

    public function handleBookPlacedOnHold(BookPlacedOnHold $event): void;

    public function handleBookHoldCanceled(BookHoldCanceled $event): void;

    public function handleBookHoldExpired(BookHoldExpired $event): void;

    public function handleBookCheckedOut(BookCheckedOut $event): void;

    public function handleBookReturned(BookReturned $event): void;
}
