<?php

namespace App\Lending\Patron\Model;

use App\Catalogue\BookId;
use App\Lending\LibraryBranch\Model\LibraryBranchId;
use Munus\Collection\Map;
use Munus\Collection\Set;
use Munus\Collection\Stream;
use Munus\Tuple;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class PatronFactory
{
    private Stream $placingOnHoldPolicies;

    public function __construct(
        #[TaggedIterator(tag: 'placing_on_hold_policy')]
        iterable $placingOnHoldPolicies
    ) {
        $this->placingOnHoldPolicies = Stream::ofAll($placingOnHoldPolicies);
    }

    /**
     * @param PatronType $patronType
     * @param PatronId $patronId
     * @param Map<string, Set<BookId>> $overdueCheckouts
     * @param Set<Tuple<BookId, LibraryBranchId>> $patronHolds
     * @return Patron
     */
    public function create(PatronType $patronType, PatronId $patronId, Map $overdueCheckouts, Set $patronHolds): Patron
    {
        return Patron::create(
            PatronInformation::create($patronType, $patronId),
            $this->placingOnHoldPolicies,
            OverdueCheckouts::create($overdueCheckouts),
            PatronHolds::create(
                $patronHolds->map(static fn (Tuple $tuple) => Hold::create($tuple[0], $tuple[1]))
            ),
        );
    }
}
