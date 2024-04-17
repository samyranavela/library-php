<?php

namespace App\Lending\Book\Application\PatronEvents;

use App\Catalogue\BookId;
use App\Lending\Book\Model\Book;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Book\Model\BookRepository;
use App\Lending\Patron\Model\Event\BookReturned;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class BookReturnedHandler
{
    public function __construct(
        private BookRepository $repository,
    ) {
    }

    public function __invoke(BookReturned $bookReturned): void
    {
        $this->repository
            ->findBy(BookId::from($bookReturned->bookId))
            ->map(fn (Book $book) => $this->handleBookReturned($book, $bookReturned))
            ->map($this->saveBook(...))
        ;
    }

    private function handleBookReturned(Book $book, BookReturned $bookReturned): Book
    {
        return match ($book::class) {
            BookOnHold::class => $book->return($bookReturned),
            default => $book,
        };
    }

    private function saveBook(Book $book): Book
    {
        $this->repository->save($book);

        return $book;
    }
}
