<?php

namespace App\Lending\Patron\Infrastructure;

use App\Commons\Event\DomainEvents;
use App\Lending\Patron\Model\Event\PatronCreated;
use App\Lending\Patron\Model\Event\PatronEvent;
use App\Lending\Patron\Model\Patron;
use App\Lending\Patron\Model\PatronId;
use App\Lending\Patron\Model\Patrons;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Munus\Control\Option;
use RuntimeException;
use Symfony\Component\Uid\Uuid;
use Throwable;

final readonly class DoctrinePatronsRepository implements Patrons
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainModelMapper $domainModelMapper,
        private DomainEvents $domainEvents,
    ) {}

    /**
     * @return Option<Patron>
     */
    public function findBy(PatronId $patronId): Option
    {
        return Option::of(
            $this
                ->findByPatronId($patronId->patronId)
                ->map($this->domainModelMapper->map(...)),
        );
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Throwable
     */
    public function publish(PatronEvent $event): Patron
    {
        $result = match ($event::class) {
            PatronCreated::class => $this->createNewPatron($event),
            default => $this->handleNextEvent($event),
        };

        $this->domainEvents->publish(...$event->normalize()->toArray());

        return $result;
    }

    /**
     * @return Option<PatronEntity>
     */
    private function findByPatronId(Uuid $patronId): Option
    {
        return Option::of(
            $this->entityManager
                ->getRepository(PatronEntity::class)
                ->findOneBy(['patronId' => $patronId]),
        );
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function createNewPatron(PatronCreated $domainEvent): Patron
    {
        $entity = PatronEntity::create($domainEvent->patronId(), $domainEvent->patronType);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->domainModelMapper->map($entity);
    }

    /**
     * @throws Throwable
     */
    private function handleNextEvent(PatronEvent $event): Patron
    {
        $entity = $this->findByPatronId($event->patronId());
        $entity = $entity
            ->getOrElseThrow(new RuntimeException(sprintf('Patron not found: %s', $event->patronId())))
            ->handle($event)
        ;
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->domainModelMapper->map($entity);
    }
}
