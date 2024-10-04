<?php

namespace App\Lending\Patron\Application\Hold;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\HoldDuration;
use App\Lending\Patron\Model\NumberOfDays;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;
use Munus\Control\Option;

final readonly class PlaceOnHoldCommand
{
    /**
     * @param Option<int> $noOfDays
     */
    private function __construct(
        public CarbonImmutable $timestamp,
        public PatronId $patronId,
        public LibraryBranchId $libraryBranchId,
        public BookId $bookId,
        public Option $noOfDays,
    ) {
    }

    public static function closeEnded(
        CarbonImmutable $timestamp,
        PatronId $patronId,
        LibraryBranchId $libraryBranchId,
        BookId $bookId,
        int $noOfDays
    ): self {
        return new self(
            $timestamp,
            $patronId,
            $libraryBranchId,
            $bookId,
            Option::of($noOfDays)
        );
    }

    public static function openEnded(
        CarbonImmutable $timestamp,
        PatronId $patronId,
        LibraryBranchId $libraryBranchId,
        BookId $bookId
    ): self {
        return new self(
            $timestamp,
            $patronId,
            $libraryBranchId,
            $bookId,
            Option::none()
        );
    }

    public function getHoldDuration(): HoldDuration
    {
        return $this->noOfDays
            ->map(NumberOfDays::of(...))
            ->map(HoldDuration::closeEndedIn(...))
            ->getOrElse(HoldDuration::openEnded())
        ;
    }
}
