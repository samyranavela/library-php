<?php

namespace App\Lending\Patron\Application\Hold;

use App\Catalogue\BookId;
use App\Commons\Command\Result;
use App\Lending\Book\Model\AvailableBook;
use App\Lending\Patron\Model\Event\BookHoldFailed;
use App\Lending\Patron\Model\Event\BookPlacedOnHoldEvents;
use App\Lending\Patron\Model\Patron;
use App\Lending\Patron\Model\PatronId;
use App\Lending\Patron\Model\Patrons;
use InvalidArgumentException;
use Munus\Control\Either\Left;
use Munus\Control\Either\Right;
use Munus\Control\TryTo;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

final class PlacingOnHold implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        public readonly FindAvailableBook $findAvailableBook,
        public readonly Patrons $patronRepository,
    ) {}

    /**
     * @return TryTo<Result>
     */
    public function placeOnHold(PlaceOnHoldCommand $command): TryTo
    {
        return TryTo::run(function () use ($command) {
            $availableBook = $this->findAvailableBook($command->bookId);
            $patron = $this->findPatron($command->patronId);

            $result = $patron->placeOnHold($availableBook, $command->getHoldDuration());

            return match ($result::class) {
                Left::class => $this->publishBookHoldFailed($result->get()),
                Right::class => $this->publishBookPlacedOnHoldEvents($result->get()),
            };
        })
            ->onFailure(
                static fn(Throwable $throwable) => $this->logger?->error(
                    'Failed to place a hold',
                    ['exception' => $throwable],
                ),
            )
        ;
    }

    private function publishBookHoldFailed(BookHoldFailed $bookHoldFailed): Result
    {
        $this->patronRepository->publish($bookHoldFailed);

        return Result::Rejection;
    }

    private function publishBookPlacedOnHoldEvents(BookPlacedOnHoldEvents $placedOnHoldEvents): Result
    {
        $this->patronRepository->publish($placedOnHoldEvents);

        return Result::Success;
    }

    /**
     * @throws Throwable
     */
    private function findAvailableBook(BookId $bookId): AvailableBook
    {
        return $this->findAvailableBook
            ->findAvailableBookBy($bookId)
            ->getOrElseThrow(
                new InvalidArgumentException(sprintf('Cannot find available book with Id: %s', $bookId->bookId)),
            )
        ;
    }

    /**
     * @throws Throwable
     */
    private function findPatron(PatronId $patronId): Patron
    {
        return $this->patronRepository
            ->findBy($patronId)
            ->getOrElseThrow(
                new InvalidArgumentException(sprintf('Patron with given Id does not exists: %s', $patronId->patronId)),
            )
        ;
    }
}
