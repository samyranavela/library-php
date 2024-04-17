<?php

namespace App\Catalogue;

final readonly class Book
{

    private function __construct(
        public ISBN $isbn,
        public Author $author,
        public Title $title,
    ) {
    }

    public static function create(ISBN|string $isbn, Author|string $author, Title|string $title): self
    {
        return new self(
            ISBN::create($isbn),
            Author::create($author),
            Title::create($title),
        );
    }
}
