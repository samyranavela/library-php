<?php

namespace App\Lending\Book\Infrastructure;

use App\Catalogue\BookId;
use App\Catalogue\BookType;
use App\Commons\Aggregates\Version;
use App\Lending\Book\Model\AvailableBook;
use App\Lending\Book\Model\Book;
use App\Lending\Book\Model\BookInformation;
use App\Lending\Book\Model\BookOnHold;
use App\Lending\Book\Model\CheckedOutBook;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use App\Lending\Patron\Model\PatronId;
use Carbon\CarbonImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Uid\Uuid;

#[Entity]
#[Table(name: 'book')]
final class BookEntity
{
    #[Id, Column(type: 'uuid')]
    public Uuid $bookId;
    #[Column(type: 'string', length: 255)]
    public string $title;
    #[Column(type: 'enum', enumType: BookType::class)]
    public BookType $bookType;
    #[Column(type: 'enum', enumType: BookState::class)]
    public BookState $bookState;
    #[Column(type: 'uuid', nullable: true)]
    public Uuid $availableAtBranch;
    #[Column(type: 'uuid', nullable: true)]
    public Uuid $onHoldAtBranch;
    #[Column(type: 'uuid', nullable: true)]
    public Uuid $onHoldByPatron;
    #[Column(type: 'datetime_immutable', nullable: true)]
    public CarbonImmutable $onHoldTill;
    #[Column(type: 'uuid', nullable: true)]
    public Uuid $checkedOutAtBranch;
    #[Column(type: 'uuid', nullable: true)]
    public Uuid $checkedOutByPatron;
    #[Column(type: 'integer')]
    public int $version;

    public function toDomainModel(): Book
    {
        return match ($this->bookState) {
            BookState::Available => $this->toAvailableBook(),
            BookState::CheckedOut => $this->toCheckedOutBook(),
            BookState::OnHold => $this->toBookOnHold(),
        };
    }

    private function toAvailableBook(): AvailableBook
    {
        return AvailableBook::create(
            BookInformation::create(
                BookId::from($this->bookId),
                $this->bookType,
            ),
            LibraryBranchId::from($this->availableAtBranch),
            Version::from($this->version),
        );
    }

    private function toCheckedOutBook(): CheckedOutBook
    {
        return CheckedOutBook::create(
            BookInformation::create(
                BookId::from($this->bookId),
                $this->bookType,
            ),
            LibraryBranchId::from($this->checkedOutAtBranch),
            PatronId::from($this->checkedOutByPatron),
            Version::from($this->version),
        );
    }

    private function toBookOnHold(): BookOnHold
    {
        return BookOnHold::create(
            BookInformation::create(
                BookId::from($this->bookId),
                $this->bookType,
            ),
            LibraryBranchId::from($this->onHoldAtBranch),
            PatronId::from($this->onHoldByPatron),
            $this->onHoldTill,
            Version::from($this->version),
        );
    }
}
