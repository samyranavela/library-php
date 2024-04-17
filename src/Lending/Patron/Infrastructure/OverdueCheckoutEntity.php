<?php

namespace App\Lending\Patron\Infrastructure;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Uid\Uuid;

#[Entity]
#[Table(name: 'overdue_checkout')]
class OverdueCheckoutEntity
{
    #[Id]
    public ?int $id = null;
    #[Column(type: 'uuid')]
    public Uuid $patronId;
    #[Column(type: 'uuid')]
    public Uuid $bookId;
    #[Column(type: 'uuid')]
    public Uuid $libraryBranchId;

    private function __construct(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId)
    {
        $this->patronId = $patronId;
        $this->bookId = $bookId;
        $this->libraryBranchId = $libraryBranchId;
    }

    public static function create(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId): self
    {
        return new self($patronId, $bookId, $libraryBranchId);
    }

    public function is(Uuid $patronId, Uuid $bookId, Uuid $libraryBranchId): bool
    {
        return $this->patronId->equals($patronId) &&
            $this->bookId->equals($bookId) &&
            $this->libraryBranchId->equals($libraryBranchId);
    }
}
