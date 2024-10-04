<?php

namespace App\Lending\DailySheet\Infrastructure;

use App\Lending\DailySheet\Model\CheckoutsToOverdueSheet;
use App\Lending\DailySheet\Model\DailySheet;
use App\Lending\DailySheet\Model\ExpiredHold;
use App\Lending\DailySheet\Model\HoldsToExpireSheet;
use App\Lending\DailySheet\Model\OverdueCheckout;
use App\Lending\Patron\Model\Event\BookCheckedOut;
use App\Lending\Patron\Model\Event\BookHoldCanceled;
use App\Lending\Patron\Model\Event\BookHoldExpired;
use App\Lending\Patron\Model\Event\BookPlacedOnHold;
use App\Lending\Patron\Model\Event\BookReturned;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Illuminate\Support\Collection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SheetsReadModel implements DailySheet, EventSubscriberInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @throws Exception
     */
    public function queryForHoldsToExpireSheet(): HoldsToExpireSheet
    {
        return HoldsToExpireSheet::create(
            $this->findHoldsToExpire()
                ->mapInto(ExpiredHold::class)
        );
    }

    /**
     * @throws Exception
     */
    public function queryForCheckoutsToOverdue(): CheckoutsToOverdueSheet
    {
        return CheckoutsToOverdueSheet::create(
            $this->findCheckoutsToOverdue()
                ->mapInto(OverdueCheckout::class)
        );
    }

    /**
     * @throws Exception
     */
    public function findCheckoutsToOverdue(): Collection
    {
        return Collection::make(
            $this->connection
                ->executeQuery(
                    "SELECT c.book_id, c.checked_out_by_patron_id, c.checked_out_at_branch FROM checkouts_sheet c WHERE c.status = 'CHECKEDOUT' AND c.checkout_till <= :checkout_till",
                    ['checkout_till' => CarbonImmutable::now()],
                    ['checkout_till' => Types::DATE_IMMUTABLE],
                )
                ->fetchAllAssociative()
        );

    }

    /**
     * @throws Exception
     */
    private function findHoldsToExpire(): Collection
    {
        return Collection::make(
            $this->connection
                ->executeQuery(
                    "SELECT h.book_id, h.hold_by_patron_id, h.hold_at_branch FROM holds_sheet h WHERE h.status = 'ACTIVE' AND h.hold_till <= :hold_till",
                    ['hold_till' => CarbonImmutable::now()],
                    ['hold_till' => Types::DATE_IMMUTABLE],
                )
                ->fetchAllAssociative()
        );
    }

    /**
     * @throws Exception
     */
    public function handleBookPlacedOnHold(BookPlacedOnHold $event): void
    {
        try {
            $this->connection->beginTransaction();
            $this->createNewHold($event);
            $this->connection->commit();
        } catch (Exception) {
            //idempotent operation
            $this->connection->rollBack();
        }
    }

    /**
     * @throws Exception
     */
    private function createNewHold(BookPlacedOnHold $event): void
    {
        $this->connection->insert(
            'holds_sheet',
            [
                'book_id' => $event->bookId,
                'status' => 'ACTIVE',
                'hold_event_id' => $event->eventId(),
                'hold_by_patron_id' => $event->patronId(),
                'hold_at' => $event->when(),
                'hold_till' => $event->holdTill,
                'hold_at_branch' => $event->libraryBranchId,
            ],
            [
                'book_id' => Types::GUID,
                'hold_event_id' => Types::GUID,
                'hold_by_patron_id' => Types::GUID,
                'hold_at' => Types::DATE_IMMUTABLE,
                'hold_till' => Types::DATE_IMMUTABLE,
                'hold_at_branch' => Types::GUID,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function handleBookHoldCanceled(BookHoldCanceled $event): void
    {
        $this->connection->update(
            'holds_sheet',
            [
                'canceled_at' => $event->when(),
                'status' => 'CANCELED',
            ],
            [
                'canceled_at' => null,
                'book_id' => $event->bookId,
                'hold_by_patron_id' => $event->patronId(),
            ],
            [
                'canceled_at' => Types::DATE_IMMUTABLE,
                'book_id' => Types::GUID,
                'hold_by_patron_id' => Types::GUID,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function handleBookHoldExpired(BookHoldExpired $event): void
    {
        $this->connection->update(
            'holds_sheet',
            [
                'expired_at' => $event->when(),
                'status' => 'EXPIRED',
            ],
            [
                'expired_at' => null,
                'book_id' => $event->bookId,
                'hold_by_patron_id' => $event->patronId(),
            ],
            [
                'canceled_at' => Types::DATE_IMMUTABLE,
                'book_id' => Types::GUID,
                'hold_by_patron_id' => Types::GUID,
            ]
        );
    }

    public function handleBookCheckedOut(BookCheckedOut $event): void
    {
        try {
            $this->createNewCheckout($event);
        } catch (Exception) {
            //idempotent operation
        }
    }

    /**
     * @throws Exception
     */
    private function createNewCheckout(BookCheckedOut $event): void
    {
        $this->connection->insert(
            'checkouts_sheet',
            [
                'book_id' => $event->bookId,
                'status' => 'CHECKEDOUT',
                'checkout_event_id' => $event->eventId(),
                'checked_out_by_patron_id' => $event->patronId(),
                'checked_out_at' => $event->when(),
                'checkout_till' => $event->till,
                'checked_out_at_branch' => $event->libraryBranchId,
            ],
            [
                'book_id' => Types::GUID,
                'hold_event_id' => Types::GUID,
                'hold_by_patron_id' => Types::GUID,
                'hold_at' => Types::DATE_IMMUTABLE,
                'hold_till' => Types::DATE_IMMUTABLE,
                'hold_at_branch' => Types::GUID,
            ]
        );

        $this->connection->update(
            'holds_sheet',
            [
                'checked_out_at' => $event->when(),
                'status' => 'CHECKEDOUT',
            ],
            [
                'status' => null,
                'book_id' => $event->bookId,
                'hold_by_patron_id' => $event->patronId(),
            ],
            [
                'checked_out_at' => Types::DATE_IMMUTABLE,
                'book_id' => Types::GUID,
                'hold_by_patron_id' => Types::GUID,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function handleBookReturned(BookReturned $event): void
    {
        $result = $this->markAsReturned($event);
        if (0 === $result) {
            $this->insertAsReturnedWithCheckedOutEventMissing($event);
        }
    }

    /**
     * @throws Exception
     */
    private function markAsReturned(BookReturned $event): int
    {
        return (int) $this->connection->update(
            'checkouts_sheet',
            [
                'returned_at' => $event->when(),
                'status' => 'RETURNED',
            ],
            [
                'status' => null,
                'book_id' => $event->bookId,
                'checked_out_by_patron_id' => $event->patronId(),
            ],
            [
                'returned_at' => Types::DATE_IMMUTABLE,
                'book_id' => Types::GUID,
                'checked_out_by_patron_id' => Types::GUID,
            ]
        );
    }

    /**
     * @throws Exception
     */
    private function insertAsReturnedWithCheckedOutEventMissing(BookReturned $event): void
    {
        $this->connection->insert(
            'checkouts_sheet',
            [
                'book_id' => $event->bookId,
                'status' => 'CHECKEDOUT',
                'checkout_event_id' => $event->eventId(),
                'checked_out_by_patron_id' => $event->patronId(),
                'checked_out_at' => $event->when(),
            ],
            [
                'book_id' => Types::GUID,
                'checkout_event_id' => Types::GUID,
                'checked_out_by_patron_id' => Types::GUID,
                'checked_out_at' => Types::DATE_IMMUTABLE,
            ]
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BookPlacedOnHold::class => 'handleBookPlacedOnHold',
            BookHoldCanceled::class => 'handleBookHoldCanceled',
            BookHoldExpired::class => 'handleBookHoldExpired',
            BookCheckedOut::class => 'handleBookCheckedOut',
            BookReturned::class => 'handleBookReturned',
        ];
    }
}
