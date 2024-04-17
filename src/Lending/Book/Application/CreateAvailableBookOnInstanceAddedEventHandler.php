<?php

namespace App\Lending\Book\Application;

use App\Catalogue\BookInstanceAddedToCatalogue;
use App\Commons\Aggregates\Version;
use App\Lending\Book\Model\AvailableBook;
use App\Lending\Book\Model\BookInformation;
use App\Lending\Book\Model\BookRepository;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateAvailableBookOnInstanceAddedEventHandler
{
    public function __construct(
        private BookRepository $repository
    ) {
    }

    public function __invoke(BookInstanceAddedToCatalogue $event): void
    {
        $this->repository
            ->save(
                AvailableBook::create(
                    BookInformation::create(
                        $event->bookId,
                        $event->type,
                    ),
                    LibraryBranchId::generate(),
                    Version::zero()
                )
            )
        ;
    }
}
