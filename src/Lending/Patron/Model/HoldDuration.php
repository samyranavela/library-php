<?php

namespace App\Lending\Patron\Model;

use Carbon\CarbonImmutable;
use Webmozart\Assert\Assert;

final readonly class HoldDuration
{
    private function __construct(
        public CarbonImmutable $from,
        public ?CarbonImmutable $to,
    ) {
        if (null !== $this->to) {
            Assert::true($this->from->isBefore($this->to), 'Close-ended duration must be valid');
        }
    }

    public static function openEnded(?CarbonImmutable $from = null): self
    {
        return self::create($from ?: CarbonImmutable::now());
    }

    public static function create(CarbonImmutable $from, ?CarbonImmutable $to = null): self
    {
        return new self($from, $to);
    }

    public static function closeEndedIn(NumberOfDays|int $till): self
    {
        return self::closeEnded(CarbonImmutable::now(), $till);
    }

    public static function closeEnded(CarbonImmutable $from, NumberOfDays|int $till): self
    {
        if (!$till instanceof NumberOfDays) {
            $till = NumberOfDays::of($till);
        }

        return self::create($from, $from->addDays($till->days));
    }

    public function isOpenEnded(): bool
    {
        return null === $this->to;
    }
}
