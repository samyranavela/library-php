<?php

namespace App\Commons\Aggregates;

final readonly class Version
{
    public function __construct(
        public int $version
    ) {
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function from(int $version): self
    {
        return new self($version);
    }

    public function next(): int
    {
        return $this->version + 1;
    }
}
