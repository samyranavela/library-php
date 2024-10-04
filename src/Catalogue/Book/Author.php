<?php

namespace App\Catalogue\Book;

use Webmozart\Assert\Assert;

final readonly class Author
{
    public string $name;

    private function __construct(
        string $name,
    ) {
        Assert::notEmpty($name, 'Author cannot be empty.');
        $this->name = trim($name);
    }

    public static function create(self|string $name): self
    {
        return is_string($name) ? new self($name) : $name;
    }
}
