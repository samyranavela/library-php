<?php

namespace App\Lending\Patron\Application\Hold;

use App\Catalogue\BookId;
use App\Commons\Command\Result;
use App\Lending\Book\Model\BookDuplicateHoldFound;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;
use Munus\Control\TryTo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class HandleDuplicateHold implements EventSubscriberInterface
{
    public function __construct(
        private CancelingHold $cancelingHold,
    ) {
    }

    /**
     * @return TryTo<Result>
     */
    public function handle(BookDuplicateHoldFound $event): TryTo
    {
        return $this->cancelingHold->cancelHold(
            CancelHoldCommand::create(
                CarbonImmutable::now(),
                PatronId::from($event->secondPatronId),
                BookId::from($event->bookId),
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BookDuplicateHoldFound::class => 'handle',
        ];
    }
}
