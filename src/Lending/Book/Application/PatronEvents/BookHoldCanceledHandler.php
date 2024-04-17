<?php

namespace App\Lending\Book\Application\PatronEvents;

use App\Catalogue\BookId;
use App\Lending\Book\Model\Book;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Book\Model\BookRepository;
use App\Lending\Patron\Model\Event\BookHoldCanceled;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class BookHoldCanceledHandler
{
    public function __construct(
        private BookRepository $repository,
    ) {
    }

    public function __invoke(BookHoldCanceled $bookHoldCanceled): void
    {
        $this->repository
            ->findBy(BookId::from($bookHoldCanceled->bookId))
            ->map(fn (Book $book) => $this->handleBookHoldCanceled($book, $bookHoldCanceled))
            ->map($this->saveBook(...))
        ;
    }

    private function handleBookHoldCanceled(Book $book, BookHoldCanceled $bookHoldCanceled): Book
    {
        return match ($book::class) {
            BookOnHold::class => $book->cancel($bookHoldCanceled),
            default => $book,
        };
    }

    private function saveBook(Book $book): Book
    {
        $this->repository->save($book);

        return $book;
    }
}
