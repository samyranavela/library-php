<?php

namespace App\Lending\Patron\Model;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use Illuminate\Support\Collection;
use Symfony\Component\Uid\Uuid;

final readonly class OverdueCheckouts
{
    public const int MAX_COUNT_OF_OVERDUE_RESOURCES = 2;

    /**
     * @param Collection<LibraryBranchId, BookId> $overdueCheckouts
     */
    public function __construct(
        public Collection $overdueCheckouts
    ) {
        $this->overdueCheckouts
            ->keys()
            ->every(static fn (string $key) => Uuid::isValid($key))
        ;
        $this->overdueCheckouts
            ->ensure(BookId::class)
        ;
    }

    public function countAt(LibraryBranchId $libraryBranchId): int
    {
        return $this->overdueCheckouts
            ->get($libraryBranchId->libraryBranchId->toRfc4122(), Collection::empty())
            ->count()
        ;
    }
}
