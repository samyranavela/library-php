<?php

namespace App\Lending\Patron\Model;

use App\Commons\Event\EitherResult;
use App\Lending\Book\Model\AvailableBook;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\Event\BookCheckedOut;
use App\Lending\Patron\Model\Event\BookCheckingOutFailed;
use App\Lending\Patron\Model\Event\BookHoldCanceled;
use App\Lending\Patron\Model\Event\BookHoldCancelingFailed;
use App\Lending\Patron\Model\Event\BookHoldFailed;
use App\Lending\Patron\Model\Event\BookPlacedOnHold;
use App\Lending\Patron\Model\Event\BookPlacedOnHoldEvents;
use App\Lending\Patron\Model\Event\MaximumNumberOhHoldsReached;
use App\Lending\Patron\Model\Policy\PlacingOnHoldPolicy;
use App\Lending\Patron\Model\Policy\Rejection;
use Munus\Collection\Stream;
use Munus\Control\Either;
use Munus\Control\Option;

final readonly class Patron
{
    /**
     * @param Stream<PlacingOnHoldPolicy> $placingOnHoldPolicies
     */
    private function __construct(
        private PatronInformation $patron,
        private Stream $placingOnHoldPolicies,
        private OverdueCheckouts $overdueCheckouts,
        private PatronHolds $patronHolds,
    ) {
    }

    /**
     * @param Stream<PlacingOnHoldPolicy> $placingOnHoldPolicies
     */
    public static function create(
        PatronInformation $patron,
        Stream $placingOnHoldPolicies,
        OverdueCheckouts $overdueCheckouts,
        PatronHolds $patronHolds,
    ): self {
        return new self($patron, $placingOnHoldPolicies, $overdueCheckouts, $patronHolds);
    }

    /**
     * @return Either<BookHoldFailed, BookPlacedOnHoldEvents>
     */
    public function placeOnHold(AvailableBook $availableBook, ?HoldDuration $holdDuration = null): Either
    {
        $holdDuration = $holdDuration ?: HoldDuration::openEnded();

        /** @var Option<Rejection> $rejection */
        $rejection = $this->patronCanHold($availableBook, $holdDuration);

        if ($rejection->isPresent()) {
            return EitherResult::announceFailure(
                BookHoldFailed::now(
                    $rejection->get(),
                    $availableBook->bookId(),
                    $availableBook->libraryBranch,
                    $this->patron
                )
            );
        }

        $bookPlacedOnHold = BookPlacedOnHold::now(
            $availableBook->bookId(),
            $availableBook->type(),
            $this->patron->patronId,
            $availableBook->libraryBranch,
            $holdDuration,
        );
        if ($this->patronHolds->maximumHoldsAfterHolding()) {
            return EitherResult::announceSuccess(
                BookPlacedOnHoldEvents::events(
                    $bookPlacedOnHold,
                    MaximumNumberOhHoldsReached::now($this->patron, PatronHolds::MAX_NUMBER_OF_HOLDS)
                ),
            );
        }

        return EitherResult::announceSuccess(
            BookPlacedOnHoldEvents::events($bookPlacedOnHold)
        );
    }

    /**
     * @return Either<BookHoldCancelingFailed, BookHoldCanceled>
     */
    public function cancelHold(BookOnHold $book): Either
    {
        if ($this->patronHolds->a($book)) {
            return EitherResult::announceSuccess(
                BookHoldCanceled::now($book->bookId(), $this->patron->patronId, $book->holdPlacedAt)
            );
        }

        return EitherResult::announceFailure(
            BookHoldCancelingFailed::now($book->bookId(), $this->patron->patronId, $book->holdPlacedAt)
        );
    }

    /**
     * @return Either<BookCheckingOutFailed, BookCheckedOut>
     */
    public function checkOut(BookOnHold $book): Either
    {
        if ($this->patronHolds->a($book)) {
            return EitherResult::announceSuccess(
                BookHoldCanceled::now($book->bookId(), $this->patron->patronId, $book->holdPlacedAt)
            );
        }

        return EitherResult::announceFailure(
            BookCheckingOutFailed::now(
                Rejection::withReason('Book is not on hold by patron.'),
                $book->bookId(),
                $this->patron->patronId,
                $book->holdPlacedAt
            )
        );
    }

    /**
     * @return Option<Rejection>
     */
    private function patronCanHold(AvailableBook $availableBook, HoldDuration $forDuration): Option
    {
        return $this->placingOnHoldPolicies
            ->map(static fn (callable $policy) => $policy($availableBook, $this, $forDuration))
            ->find(static fn (Either $either) => $either->isLeft())
            ->map(static fn (Either $either) => $either->getLeft())
        ;
    }

    public function isRegular(): bool
    {
        return $this->patron->isRegular();
    }

    public function overdueCheckoutsAt(LibraryBranchId $libraryBranch): int
    {
        return $this->overdueCheckouts->countAt($libraryBranch);
    }

    public function numberOfHolds(): int
    {
        return $this->patronHolds->count();
    }
}
