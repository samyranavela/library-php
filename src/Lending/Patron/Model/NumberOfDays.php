<?php

namespace App\Lending\Patron\Model;

use Webmozart\Assert\Assert;

final readonly class NumberOfDays
{
    private function __construct(
        public int $days
    ) {
        Assert::greaterThan($days, 0, 'Cannot use negative integer or zero as number of days');
    }

    public static function of(int $days): self
    {
        return new self($days);
    }

    public function isGreaterThan(int $days): bool
    {
        return $this->days > $days;
    }
}
