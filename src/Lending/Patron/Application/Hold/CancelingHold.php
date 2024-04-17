<?php

namespace App\Lending\Patron\Application\Hold;

use App\Catalogue\BookId;
use App\Commons\Command\Result;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Patron\Model\Event\BookHoldCanceled;
use App\Lending\Patron\Model\Event\BookHoldCancelingFailed;
use App\Lending\Patron\Model\Patron;
use App\Lending\Patron\Model\PatronId;
use App\Lending\Patron\Model\Patrons;
use InvalidArgumentException;
use Munus\Control\Either\Left;
use Munus\Control\Either\Right;
use Munus\Control\TryTo;
use Throwable;

final readonly class CancelingHold
{
    public function __construct(
        public FindBookOnHold $findBookOnHold,
        public Patrons $patronRepository,
    ) {
    }

    /**
     * @return TryTo<Result>
     */
    public function cancelHold(CancelHoldCommand $command): TryTo
    {
        return TryTo::run(function () use ($command) {
            $bookOnHold = $this->findBookOnHold($command->bookId, $command->patronId);
            $patron = $this->findPatron($command->patronId);

            $result = $patron->cancelHold($bookOnHold);

            return match ($result::class) {
                Left::class => $this->publishBookHoldCancelingFailed($result->get()),
                Right::class => $this->publishBookHoldCanceled($result->get()),
            };
        });
    }

    private function publishBookHoldCancelingFailed(BookHoldCancelingFailed $bookHoldCancelingFailed): Result
    {
        $this->patronRepository->publish($bookHoldCancelingFailed);

        return Result::Rejection;
    }

    private function publishBookHoldCanceled(BookHoldCanceled $bookHoldCanceled): Result
    {
        $this->patronRepository->publish($bookHoldCanceled);

        return Result::Success;
    }

    /**
     * @throws Throwable
     */
    private function findBookOnHold(BookId $bookId, PatronId $patronId): BookOnHold
    {
        return $this->findBookOnHold
            ->findBookOnHold($bookId, $patronId)
            ->getOrElseThrow(new InvalidArgumentException(sprintf('Cannot find book on hold with Id: %s', $bookId->bookId)))
        ;
    }

    /**
     * @throws Throwable
     */
    private function findPatron(PatronId $patronId): Patron
    {
        return $this->patronRepository
            ->findBy($patronId)
            ->getOrElseThrow(new InvalidArgumentException(sprintf('Patron with given Id does not exists: %s', $patronId->patronId)))
        ;
    }
}
