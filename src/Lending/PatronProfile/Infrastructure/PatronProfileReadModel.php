<?php

namespace App\Lending\PatronProfile\Infrastructure;

use App\Catalogue\BookId;
use App\Lending\Patron\Model\PatronId;
use App\Lending\PatronProfile\Model\Checkout;
use App\Lending\PatronProfile\Model\CheckoutsView;
use App\Lending\PatronProfile\Model\Hold;
use App\Lending\PatronProfile\Model\HoldsView;
use App\Lending\PatronProfile\Model\PatronProfile;
use App\Lending\PatronProfile\Model\PatronProfiles;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Illuminate\Support\Collection;

final readonly class PatronProfileReadModel implements PatronProfiles
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function fetchFor(PatronId $patronId): PatronProfile
    {
        $holdsView = HoldsView::create(
            Collection::make($this->findCurrentHoldsFor($patronId))
                ->map($this->toHold(...))
        );
        $checkoutsView = CheckoutsView::create(
            Collection::make($this->findCurrentHoldsFor($patronId))
                ->map($this->toHold(...))
        );

        return PatronProfile::create($holdsView, $checkoutsView);
    }

    /**
     * @throws Exception
     */
    private function findCurrentHoldsFor(PatronId $patronId): array
    {
        return $this->connection
            ->executeQuery(
                'SELECT h.book_id, h.hold_till FROM holds_sheet h WHERE h.hold_by_patron_id = ? AND h.checked_out_at IS NULL AND h.expired_at IS NULL AND h.canceled_at IS NULL',
                ['hold_by_patron_id' => $patronId->patronId],
                ['hold_by_patron_id' => Types::GUID],
            )->fetchAllAssociative()
        ;
    }

    private function toHold(array $map): Hold
    {
        return Hold::create(
            BookId::from($map['book_id']),
            CarbonImmutable::parse($map['hold_till']),
        );
    }

    /**
     * @throws Exception
     */
    private function findCurrentCheckoutsFor(PatronId $patronId): array
    {
        return $this->connection
            ->executeQuery(
                'SELECT h.book_id, h.checkout_till FROM checkouts_sheet h WHERE h.checked_out_by_patron_id = ? AND h.returned_at IS NULL',
                ['checked_out_by_patron_id' => $patronId->patronId],
                ['checked_out_by_patron_id' => Types::GUID],
            )->fetchAllAssociative()
        ;
    }

    private function toCheckout(array $map): Checkout
    {
        return Checkout::create(
            BookId::from($map['book_id']),
            CarbonImmutable::parse($map['hold_till']),
        );
    }
}
