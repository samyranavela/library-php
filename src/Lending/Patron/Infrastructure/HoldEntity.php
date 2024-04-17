<?php

namespace App\Lending\Patron\Infrastructure;

use Carbon\CarbonImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Uid\Uuid;

#[Entity]
#[Table(name: 'hold')]
class HoldEntity
{
    #[Id]
    public ?int $id = null;
    #[Column(type: 'uuid')]
    public Uuid $patronId;
    #[Column(type: 'uuid')]
    public Uuid $bookId;
    #[Column(type: 'uuid')]
    public Uuid $libraryBranchId;
    #[Column(type: 'datetime_immutable', nullable: true)]
    public ?CarbonImmutable $till;

    private function __construct(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId, ?CarbonImmutable $till)
    {
        $this->patronId = $patronId;
        $this->bookId = $bookId;
        $this->libraryBranchId = $libraryBranchId;
        $this->till = $till;
    }

    public static function create(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId, ?CarbonImmutable $till): self
    {
        return new self($patronId, $bookId, $libraryBranchId, $till);
    }

    public function is(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId): bool
    {
        return $this->patronId->equals($patronId) &&
            $this->bookId->equals($bookId) &&
            $this->libraryBranchId->equals($libraryBranchId);
    }
}
