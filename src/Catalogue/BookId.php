<?php

namespace App\Catalogue;

use App\Commons\Equatable;
use Symfony\Component\Uid\Uuid;

/**
 * @implements Equatable<BookId>
 */
final readonly class BookId implements Equatable
{
    private function __construct(
        public Uuid $bookId,
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v7());
    }

    public static function from(Uuid $bookId): self
    {
        return new self($bookId);
    }

    /**
     * @param Equatable<BookId> $other
     */
    public function equals(Equatable $other): bool
    {
        return $other instanceof self && $this->bookId->equals($other->bookId);
    }
}
