<?php

namespace App\Lending\Book\Application\PatronEvents;

use App\Catalogue\BookId;
use App\Lending\Book\Model\Book;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Book\Model\BookRepository;
use App\Lending\Patron\Model\Event\BookCheckedOut;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class BookCheckedOutHandler
{
    public function __construct(
        private BookRepository $repository,
    ) {
    }

    public function __invoke(BookCheckedOut $bookCheckedOut): void
    {
        $this->repository
            ->findBy(BookId::from($bookCheckedOut->bookId))
            ->map(fn (Book $book) => $this->handleBookCheckedOut($book, $bookCheckedOut))
            ->map($this->saveBook(...))
        ;
    }

    private function handleBookCheckedOut(Book $book, BookCheckedOut $bookCheckedOut): Book
    {
        return match ($book::class) {
            BookOnHold::class => $book->checkout($bookCheckedOut),
            default => $book,
        };
    }

    private function saveBook(Book $book): Book
    {
        $this->repository->save($book);

        return $book;
    }
}
