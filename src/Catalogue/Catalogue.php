<?php

namespace App\Catalogue;

use App\Catalogue\Book\Book;
use App\Catalogue\CatalogDatabase\CatalogDatabase;
use App\Commons\Command\Result;
use App\Commons\Event\DomainEvents;
use Doctrine\DBAL\Exception;
use Munus\Control\TryTo;

final readonly class Catalogue
{
    public function __construct(
        private CatalogDatabase $repository,
        private DomainEvents $domainEvents,
    ) {
    }

    /**
     * @return TryTo<Result>
     */
    public function addBook(string $author, string $title, string $isbn): TryTo
    {
        return TryTo::run(
            function () use ($author, $title, $isbn) {
                $book = Book::create($author, $title, $isbn);
                $this->repository->saveNewBook($book);

                return Result::Success;
            }
        );
    }

    /**
     * @return TryTo<Result>
     */
    public function addBookInstance(string $isbn, BookType $bookType): TryTo
    {
        return TryTo::run(
            $this->repository
                ->findBy(ISBN::create($isbn))
                ->map(static fn (Book $book): BookInstance => BookInstance::instanceOf($book, $bookType))
                ->map($this->saveAndPublishEvent(...))
                ->map(static fn (): Result => Result::Success)
                ->getOrElse(Result::Rejection)
        );
    }

    /**
     * @throws Exception
     */
    private function saveAndPublishEvent(BookInstance $bookInstance): BookInstance
    {
        $this->repository->saveNewBookInstance($bookInstance);
        $this->domainEvents->publish(BookInstanceAddedToCatalogue::now($bookInstance));

        return $bookInstance;
    }
}
