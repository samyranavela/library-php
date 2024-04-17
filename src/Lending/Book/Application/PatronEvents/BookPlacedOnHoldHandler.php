<?php

namespace App\Lending\Book\Application\PatronEvents;

use App\Catalogue\BookId;
use App\Commons\Event\DomainEvents;
use App\Lending\Book\Model\AvailableBook;
use App\Lending\Book\Model\Book;
use App\Lending\Book\Model\BookDuplicateHoldFound;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Book\Model\BookRepository;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\Event\BookPlacedOnHold;
use App\Lending\Patron\Model\PatronId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class BookPlacedOnHoldHandler
{
    public function __construct(
        private BookRepository $repository,
        private DomainEvents $domainEvents,
    ) {
    }

    public function __invoke(BookPlacedOnHold $bookPlacedOnHold): void
    {
        $this->repository
            ->findBy(BookId::from($bookPlacedOnHold->bookId))
            ->map(fn (Book $book) => $this->handleBookPlacedOnHold($book, $bookPlacedOnHold))
            ->map($this->saveBook(...))
        ;
    }

    private function handleBookPlacedOnHold(Book $book, BookPlacedOnHold $bookPlacedOnHold): Book
    {
        return match ($book::class) {
            AvailableBook::class => $book->handle($bookPlacedOnHold),
            BookOnHold::class => $this->raiseDuplicateHoldFoundEvent($book, $bookPlacedOnHold),
            default => $book,
        };
    }

    private function raiseDuplicateHoldFoundEvent(BookOnHold $bookOnHold, BookPlacedOnHold $bookPlacedOnHold): BookOnHold
    {
        if ($bookOnHold->by(PatronId::from($bookPlacedOnHold->aggregateId()))) {
            return $bookOnHold;
        }

        $this->domainEvents->publish(
            BookDuplicateHoldFound::now(
                $bookOnHold->bookId(),
                $bookOnHold->byPatron,
                PatronId::from($bookPlacedOnHold->patronId),
                LibraryBranchId::from($bookPlacedOnHold->libraryBranchId),
            )
        );

        return $bookOnHold;
    }

    private function saveBook(Book $book): Book
    {
        $this->repository->save($book);

        return $book;
    }
}
