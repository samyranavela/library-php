<?php

namespace App\Lending\Patron\Application\Checkout;

use App\Catalogue\BookId;
use App\Commons\Command\Result;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Patron\Application\Hold\FindBookOnHold;
use App\Lending\Patron\Model\Event\BookCheckedOut;
use App\Lending\Patron\Model\Event\BookCheckingOutFailed;
use App\Lending\Patron\Model\Patron;
use App\Lending\Patron\Model\PatronId;
use App\Lending\Patron\Model\Patrons;
use InvalidArgumentException;
use Munus\Control\Either\Left;
use Munus\Control\Either\Right;
use Munus\Control\TryTo;
use Throwable;

final readonly class CheckingOutBookOnHold
{
    public function __construct(
        private FindBookOnHold $findBookOnHold,
        private Patrons $patronRepository,
    ) {
    }

    /**
     * @return TryTo<Result>
     */
    public function checkOut(CheckOutBookCommand $command): TryTo
    {
        return TryTo::run(function () use ($command) {
            $bookOnHold = $this->findBookOnHold($command->bookId, $command->patronId);
            $patron = $this->findPatron($command->patronId);

            $result = $patron->checkOut($bookOnHold);

            return match ($result::class) {
                Left::class => $this->publishBookCheckingOutFailed($result->get()),
                Right::class => $this->publishBookCheckedOut($result->get()),
            };
        });
    }

    private function publishBookCheckingOutFailed(BookCheckingOutFailed $bookCheckingOutFailed): Result
    {
        $this->patronRepository->publish($bookCheckingOutFailed);

        return Result::Rejection;
    }

    private function publishBookCheckedOut(BookCheckedOut $bookCheckedOut): Result
    {
        $this->patronRepository->publish($bookCheckedOut);

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
