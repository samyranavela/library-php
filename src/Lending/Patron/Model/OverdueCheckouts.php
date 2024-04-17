<?php

namespace App\Lending\Patron\Model;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use Munus\Collection\Map;
use Munus\Collection\Set;

final readonly class OverdueCheckouts
{
    public const int MAX_COUNT_OF_OVERDUE_RESOURCES = 2;

    /**
     * @param Map<string, Set<BookId>> $overdueCheckouts
     */
    private function __construct(
        public Map $overdueCheckouts
    ) {
    }

    /**
     * @param Map<string, Set<BookId>> $overdueCheckouts
     */
    public static function create(Map $overdueCheckouts): self
    {
        return new self($overdueCheckouts);
    }

    public function countAt(LibraryBranchId $libraryBranchId): int
    {
        return $this->overdueCheckouts
            ->get($libraryBranchId->libraryBranchId->toRfc4122())
            ->getOrElse(Set::empty())
            ->length()
        ;
    }
}
