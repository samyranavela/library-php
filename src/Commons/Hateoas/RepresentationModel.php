<?php

namespace App\Commons\Hateoas;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Munus\Control\Option;
use Psr\Link\EvolvableLinkInterface;
use Stringable;

readonly class RepresentationModel implements Stringable
{
    public Collection $links;

    public function __construct(?EvolvableLinkInterface ...$links)
    {
        $this->links = $links ? Collection::make($links) : Collection::empty();
    }

    public function add(EvolvableLinkInterface ...$links): self
    {
        $this->links->push(...$links);

        return $this;
    }

    public function addIf(callable $guard, EvolvableLinkInterface ...$links): self
    {
        if ($guard) {
            $this->add(...$links);
        }

        return $this;
    }

    public function hasLinks(): bool
    {
        return !$this->links->isEmpty();
    }

    public function hasLink(LinkRelationInterface|string $relation): bool
    {
        return $this->getLink($this->getLinkRelation($relation))->isPresent();
    }

    /**
     * @return Option<EvolvableLinkInterface>
     */
    public function getLink(LinkRelationInterface|string $relation): Option
    {
        return Option::of(
            $this->links->first(
                static fn (EvolvableLinkInterface $link) => in_array($this->getLinkRelation($relation), $link->getRels(), true)
            )
        );
    }

    public function getRequiredLink(LinkRelationInterface|string $relation): EvolvableLinkInterface
    {
        return $this->getLink($this->getLinkRelation($relation))
            ->getOrElseThrow(
                new InvalidArgumentException(sprintf('No link with rel %s found!', $relation))
            )
        ;
    }

    public function getLinks(LinkRelationInterface|string $relation): Collection
    {
        return $this->links->filter(
            static fn (EvolvableLinkInterface $link) => in_array($this->getLinkRelation($relation), $link->getRels(), true)
        );
    }

    private function getLinkRelation(LinkRelationInterface|string $relation): LinkRelationInterface
    {
        return $relation instanceof LinkRelationInterface ? $relation : LinkRelation::of($relation);
    }

    public function __toString(): string
    {
        return sprintf('links: %s', $this->links->join(','));
    }
}
