<?php

namespace App\Lending\DailySheet\Model;

use App\Lending\Patron\Model\Event\OverdueCheckoutRegistered;
use Illuminate\Support\Collection;
use Munus\Collection\Stream;

final readonly class CheckoutsToOverdueSheet
{
    /**
     * @param Collection<OverdueCheckout> $checkouts
     */
    private function __construct(
        private Collection $checkouts,
    ) {
        $this->checkouts
            ->ensure(OverdueCheckout::class)
        ;
    }

    public static function create(Collection $checkouts): self
    {
        return new self($checkouts);
    }

    /**
     * @return Stream<OverdueCheckoutRegistered>
     */
    public function toStreamOfEvents(): Stream
    {
        return Stream::ofAll(
            $this->checkouts
                ->map(static fn (OverdueCheckout $overdueCheckout): OverdueCheckoutRegistered => $overdueCheckout->toEvent())
        );
    }

    public function count(): int
    {
        return $this->checkouts->count();
    }
}
