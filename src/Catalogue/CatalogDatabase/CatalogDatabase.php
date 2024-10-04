<?php

namespace App\Catalogue\CatalogDatabase;

use App\Catalogue\Book\Book;
use App\Catalogue\BookInstance;
use App\Catalogue\ISBN;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Munus\Control\Option;
use Symfony\Bridge\Doctrine\Types\UuidType;

final readonly class CatalogDatabase
{
    public function __construct(
        private Connection $connection,
    ) {}

    /**
     * @throws Exception
     */
    public function saveNewBook(Book $book): Book
    {
        $this->connection
            ->createQueryBuilder()
            ->insert('catalog_book')
            ->values(
                [
                    'isbn' => ':isbn',
                    'title' => ':title',
                    'author' => ':author',
                ],
            )
            ->setParameters(
                [
                    'isbn' => $book->isbn->isbn,
                    'title' => $book->title,
                    'author' => $book->author->name,
                ],
                [
                    'isbn' => Types::STRING,
                    'title' => Types::STRING,
                    'author' => Types::STRING,
                ],
            )
            ->executeQuery()
        ;

        return $book;
    }

    /**
     * @throws Exception
     */
    public function saveNewBookInstance(BookInstance $bookInstance): BookInstance
    {
        $this->connection
            ->createQueryBuilder()
            ->insert('catalogue_book_instance')
            ->values(
                [
                    'isbn' => ':isbn',
                    'book_id' => ':book_id',
                ],
            )
            ->setParameters(
                [
                    'isbn' => $bookInstance->isbn->isbn,
                    'book_id' => $bookInstance->bookId->bookId,
                ],
                [
                    'isbn' => Types::STRING,
                    'book_id' => UuidType::NAME,
                ],
            )
            ->executeQuery()
        ;

        return $bookInstance;
    }

    /**
     * @return Option<Book>
     */
    public function findBy(ISBN $isbn): Option
    {
        try {
            return Option::of(
                array_map(
                    $this->toBook(...),
                    $this->connection
                        ->createQueryBuilder()
                        ->select('*')
                        ->from('catalog_book')
                        ->where('isbn = :isbn')
                        ->setParameter('isbn', $isbn->isbn)
                        ->executeQuery()
                        ->fetchAllAssociative(),
                ),
            );
        } catch (\Throwable) {
            return Option::none();
        }
    }

    private function toBook(array $data): Book
    {
        return Book::create(
            $data['isbn'],
            $data['author'],
            $data['title'],
        );
    }
}
