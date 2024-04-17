<?php

namespace App\Lending\LibraryBranch\Model;

use App\Commons\Equatable;
use Symfony\Component\Uid\Uuid;

/**
 * @implements Equatable<LibraryBranchId>
 */
final readonly class LibraryBranchId implements Equatable
{
    private function __construct(
        public Uuid $libraryBranchId,
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v7());
    }

    public static function from(Uuid $libraryBranchId): self
    {
        return new self($libraryBranchId);
    }

    /**
     * @param Equatable<LibraryBranchId> $other
     */
    public function equals(Equatable $other): bool
    {
        return $other instanceof self && $this->libraryBranchId->equals($other->libraryBranchId);
    }
}
