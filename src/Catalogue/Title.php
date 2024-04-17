<?php

namespace App\Catalogue;

use Webmozart\Assert\Assert;

final readonly class Title
{
    public string $title;

    private function __construct(
        string $title,
    ) {
        Assert::notEmpty($title, 'Title cannot be empty.');
        $this->title = trim($title);
    }

    public static function create(self|string $title): self
    {
        return is_string($title) ? new self($title) : $title;
    }
}
