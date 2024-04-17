<?php

namespace App\Catalogue;

use Webmozart\Assert\Assert;

final readonly class ISBN
{
    private const string VERY_SIMPLE_ISBN_CHECK = "^\\d{9}[\\d|X]$";
    public string $isbn;

    private function __construct(
        string $isbn
    ) {
        Assert::regex($isbn, self::VERY_SIMPLE_ISBN_CHECK, "Wrong ISBN!");

        $this->isbn = trim($isbn);
    }

    public static function create(self|string $isbn): self
    {
        return new self(is_string($isbn) ? $isbn : $isbn->isbn);
    }
}
