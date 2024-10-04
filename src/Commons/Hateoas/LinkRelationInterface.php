<?php

namespace App\Commons\Hateoas;

interface LinkRelationInterface
{
    public static function of(string $rel): self;

    public function value(): string;
}
