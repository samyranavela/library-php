<?php

namespace App\Lending\Book\Application\PatronEvents;

use App\Catalogue\BookId;
use App\Lending\Book\Model\Book;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Book\Model\BookRepository;
use App\Lending\Patron\Model\Event\BookHoldExpired;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class BookHoldExpiredHandler
{
    public function __construct(
        private BookRepository $repository,
    ) {
    }

    public function __invoke(BookHoldExpired $bookHoldExpired): void
    {
        $this->repository
            ->findBy(BookId::from($bookHoldExpired->bookId))
            ->map(fn (Book $book) => $this->handleBookHoldExpired($book, $bookHoldExpired))
            ->map($this->saveBook(...))
        ;
    }

    private function handleBookHoldExpired(Book $book, BookHoldExpired $bookHoldExpired): Book
    {
        return match ($book::class) {
            BookOnHold::class => $book->expire($bookHoldExpired),
            default => $book,
        };
    }

    private function saveBook(Book $book): Book
    {
        $this->repository->save($book);

        return $book;
    }
}
