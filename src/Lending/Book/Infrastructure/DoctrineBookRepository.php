<?php

namespace App\Lending\Book\Infrastructure;

use App\Catalogue\BookId;
use App\Catalogue\BookType;
use App\Commons\Aggregates\AggregateRootIsStale;
use App\Lending\Book\Model\AvailableBook;
use App\Lending\Book\Model\Book;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Book\Model\BookRepository;
use App\Lending\Book\Model\CheckedOutBook;
use App\Lending\Patron\Application\Hold\FindAvailableBook;
use App\Lending\Patron\Application\Hold\FindBookOnHold;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Munus\Control\Option;
use Munus\Control\TryTo;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineBookRepository implements BookRepository, FindAvailableBook, FindBookOnHold
{
    private EntityRepository $entityRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->entityRepository = $entityManager->getRepository(BookEntity::class);
    }

    /**
     * @return Option<Book>
     */
    public function findBy(BookId $bookId): Option
    {
        return $this->findBookById($bookId)
            ->map(static fn (BookEntity $entity): Book => $entity->toDomainModel())
        ;
    }

    /**
     * @return Option<BookEntity>
     */
    private function findBookById(BookId $bookId): Option
    {
        return TryTo::run($this->entityRepository->find($bookId))
            ->getOrElse(Option::none())
        ;
    }

    /**
     * @throws Exception
     */
    public function save(Book $book): void
    {
        $this->findBookById($book->bookId())
            ->map(static fn () => $this->updateOptimistically($book))
            ->getOrElseTry(static fn () => $this->insertNew($book))
        ;
    }

    /**
     * @return Option<AvailableBook>
     */
    public function findAvailableBookBy(BookId $bookId): Option
    {
        return TryTo::run(
            $this->entityRepository
                ->findBy(['bookId' => $bookId, 'bookState' => BookState::Available])
        )
            ->map(static fn (BookEntity $entity): Book => $entity->toDomainModel())
            ->getOrElse(Option::none())
        ;
    }

    /**
     * @return Option<BookOnHold>
     */
    public function findBookOnHold(BookId $bookId, ?PatronId $patronId = null): Option
    {
        return TryTo::run(
            $this->entityRepository
                ->findBy(
                    array_filter(['bookId' => $bookId, 'bookState' => BookState::OnHold, 'patronId' => $patronId]),
                )
        )
            ->map(static fn (BookEntity $entity): Book => $entity->toDomainModel())
            ->getOrElse(Option::none())
        ;
    }

    /**
     * @throws Exception
     */
    private function updateOptimistically(Book $book): int
    {
        $result = match ($book::class) {
            AvailableBook::class => $this->updateAvailableBook($book),
            BookOnHold::class => $this->updateBookOnHold($book),
            CheckedOutBook::class => $this->updateCheckedOutBook($book),
            default => 0
        };

        if (0 === $result) {
            throw  new AggregateRootIsStale(sprintf('Someone has updated book in the meantime, book: %s', $book->bookId()->bookId));
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    private function updateAvailableBook(AvailableBook $book): int
    {
        return (int) $this->entityManager->getConnection()
            ->update(
                'book',
                [
                    'bookState' => BookState::Available,
                    'availableAtBranch' => $book->libraryBranch->libraryBranchId,
                    'version' => $book->version->next(),
                ],
                [
                    'bookId' => $book->bookId(),
                ]
            )
        ;
    }

    /**
     * @throws Exception
     */
    private function updateBookOnHold(BookOnHold $book): int
    {
        return (int) $this->entityManager->getConnection()
            ->update(
                'book',
                [
                    'bookState' => BookState::OnHold,
                    'availableAtBranch' => $book->holdPlacedAt->libraryBranchId,
                    'onHoldByPatron' => $book->byPatron->patronId,
                    'onHoldTill' => $book->holdTill,
                    'version' => $book->version->next(),
                ],
                [
                    'bookId' => $book->bookId(),
                    'version' => $book->version,
                ]
            )
        ;
    }

    /**
     * @throws Exception
     */
    private function updateCheckedOutBook(CheckedOutBook $book): int
    {
        return (int) $this->entityManager->getConnection()
            ->update(
                'book',
                [
                    'bookState' => BookState::CheckedOut,
                    'availableAtBranch' => $book->checkedOutAt->libraryBranchId,
                    'onHoldByPatron' => $book->byPatron->patronId,
                    'version' => $book->version->next(),
                ],
                [
                    'bookId' => $book->bookId(),
                    'version' => $book->version,
                ]
            )
        ;
    }

    /**
     * @throws Exception
     */
    private function insertNew(Book $book): void
    {
        match ($book::class) {
            AvailableBook::class => $this->insertAvailableBook($book),
            BookOnHold::class => $this->insertBookOnHold($book),
            CheckedOutBook::class => $this->insertCheckedOutBook($book),
        };
    }

    /**
     * @throws Exception
     */
    private function insertAvailableBook(AvailableBook $book): void
    {
        $this->insert(
            $book->bookId(),
            $book->type(),
            BookState::Available,
            $book->libraryBranch->libraryBranchId,
        );
    }

    /**
     * @throws Exception
     */
    private function insertBookOnHold(BookOnHold $book): void
    {
        $this->insert(
            $book->bookId(),
            $book->type(),
            BookState::OnHold,
            null,
            $book->holdPlacedAt->libraryBranchId,
            $book->byPatron->patronId,
            $book->holdTill,
        );
    }

    /**
     * @throws Exception
     */
    private function insertCheckedOutBook(CheckedOutBook $book): void
    {
        $this->insert(
            $book->bookId(),
            $book->type(),
            BookState::CheckedOut,
            null,
            null,
            null,
            null,
            $book->checkedOutAt->libraryBranchId,
            $book->byPatron->patronId,
        );
    }

    /**
     * @throws Exception
     */
    private function insert(
        BookId $bookId,
        BookType $bookType,
        BookState $state,
        ?Uuid $availableAt = null,
        ?Uuid $onHoldAt = null,
        ?Uuid $onHoldBy = null,
        ?CarbonImmutable $onHoldTill = null,
        ?Uuid $checkedOutAt = null,
        ?Uuid $checkedOutBy = null,
    ): void {
        $this->entityManager->getConnection()
            ->insert(
                'book',
                [
                    'bookId' => $bookId,
                    'bookType' => $bookType,
                    'bookState' => $state,
                    'availableAtBranch' => $availableAt,
                    'onHoldAtBranch' => $onHoldAt,
                    'onHoldByPatron' => $onHoldBy,
                    'onHoldTill' => $onHoldTill,
                    'checkedOutAtBranch' => $checkedOutAt,
                    'checkedOutByPatron' => $checkedOutBy,
                ]
            )
        ;
    }
}
