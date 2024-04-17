<?php

namespace App\Lending\Patron\Model;

use Carbon\CarbonImmutable;
use Webmozart\Assert\Assert;

final readonly class CheckoutDuration
{
    public const int MAX_CHECKOUT_DURATION = 60;

    private function __construct(
        public int $numberOfDays,
        public CarbonImmutable $from,
        public CarbonImmutable $to,
    ) {
        Assert::greaterThan(
            $numberOfDays,
            self::MAX_CHECKOUT_DURATION,
            sprintf('Cannot checkout for more than %d days', self::MAX_CHECKOUT_DURATION)
        );
    }

    public static function forNumberOfDays(NumberOfDays|int $numberOfDays): self
    {
        return self::create(CarbonImmutable::now(), NumberOfDays::of($numberOfDays));
    }

    public static function create(CarbonImmutable $from, NumberOfDays|int $numberOfDays): self
    {
        if (!$numberOfDays instanceof NumberOfDays) {
            $numberOfDays = NumberOfDays::of($numberOfDays);
        }

        return new self($numberOfDays, $from, $from->addDays($numberOfDays->days));
    }
}
