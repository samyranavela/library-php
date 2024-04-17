<?php

namespace App\Lending\Patron\Infrastructure;

use App\Lending\Patron\Model\Event\BookCheckedOut;
use App\Lending\Patron\Model\Event\BookHoldCanceled;
use App\Lending\Patron\Model\Event\BookHoldExpired;
use App\Lending\Patron\Model\Event\BookPlacedOnHold;
use App\Lending\Patron\Model\Event\BookPlacedOnHoldEvents;
use App\Lending\Patron\Model\Event\BookReturned;
use App\Lending\Patron\Model\Event\OverdueCheckoutRegistered;
use App\Lending\Patron\Model\Event\PatronEvent;
use App\Lending\Patron\Model\PatronType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Uid\Uuid;

#[Entity]
#[Table(name: 'patron')]
class PatronEntity
{
    #[Id]
    public ?int $id = null;
    #[Column(type: 'uuid')]
    public Uuid $patronId;
    #[Column(type: 'enum', enumType: PatronType::class)]
    public PatronType $patronType;
    #[OneToMany(targetEntity: HoldEntity::class)]
    /** @var Collection&Selectable<array-key, HoldEntity> $booksOnHold */
    public Collection&Selectable $booksOnHold;
    #[OneToMany(targetEntity: OverdueCheckoutEntity::class)]
    /** @var Collection&Selectable<array-key, OverdueCheckoutEntity> $checkouts */
    public Collection&Selectable $checkouts;

    private function __construct(
        Uuid $patronId,
        PatronType $patronType,
        Collection&Selectable $booksOnHold,
        Collection&Selectable $checkouts,
    ) {
        $this->patronId = $patronId;
        $this->patronType = $patronType;
        $this->booksOnHold = $booksOnHold;
        $this->checkouts = $checkouts;
    }

    public static function create(
        Uuid $patronId,
        PatronType $patronType,
        Collection&Selectable $booksOnHold = new ArrayCollection(),
        Collection&Selectable $checkouts = new ArrayCollection(),
    ): self {
        return new self(
            $patronId,
            $patronType,
            $booksOnHold,
            $checkouts
        );
    }

    public function handle(PatronEvent $event): self
    {
        return match ($event::class) {
            BookPlacedOnHoldEvents::class => $this->handle($event->bookPlacedOnHold),
            BookPlacedOnHold::class => $this->addBookToHold($event),
            BookHoldCanceled::class => $this->removeHoldIfPresent($event->patronId(), $event->bookId, $event->libraryBranchId),
            BookCheckedOut::class => $this->removeHoldIfPresent($event->patronId(), $event->bookId, $event->libraryBranchId),
            BookHoldExpired::class => $this->removeHoldIfPresent($event->patronId(), $event->bookId, $event->libraryBranchId),
            OverdueCheckoutRegistered::class => $this->addBookToCheckout($event),
            BookReturned::class => $this->removeOverdueIfPresent($event->patronId(), $event->bookId, $event->libraryBranchId),
        };
    }

    private function addBookToHold(BookPlacedOnHold $event): self
    {
        $this->booksOnHold->add(HoldEntity::create($event->bookId, $event->patronId(), $event->libraryBranchId, $event->holdTill));

        return $this;
    }

    private function addBookToCheckout(OverdueCheckoutRegistered $event): self
    {
        $this->checkouts->add(OverdueCheckoutEntity::create($event->bookId, $event->patronId(), $event->libraryBranchId));

        return $this;
    }

    private function removeHoldIfPresent(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId): self
    {
        $this->booksOnHold
            ->matching($this->buildCriteria($patronId, $bookId, $libraryBranchId))
            ->forAll(static fn (HoldEntity $entity) => $this->booksOnHold->remove($entity))
        ;

        return $this;
    }

    private function removeOverdueIfPresent(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId): self
    {
        $this->booksOnHold
            ->matching($this->buildCriteria($patronId, $bookId, $libraryBranchId))
            ->forAll(static fn (OverdueCheckoutEntity $entity) => $this->booksOnHold->remove($entity))
        ;

        return $this;
    }

    private function buildCriteria(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId): Criteria
    {
        return Criteria::create()
            ->where(Criteria::expr()?->eq('patronId', $patronId))
            ->andWhere(Criteria::expr()?->eq('bookId', $bookId))
            ->andWhere(Criteria::expr()?->eq('libraryBranchId', $libraryBranchId))
        ;
    }
}
